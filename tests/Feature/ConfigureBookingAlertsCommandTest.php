<?php

namespace Tests\Feature;

use App\Models\AdminCredential;
use App\Models\OrderNotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfigureBookingAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_values_configure_recipients_and_admin_login(): void
    {
        $passwordHash = Hash::make('A-secure-admin-password');

        $this->artisan('app:configure-booking-alerts', [
            'recipients' => base64_encode(' Owner@Example.com,backup@example.com,owner@example.com '),
            'passwordHash' => base64_encode($passwordHash),
        ])->assertSuccessful();

        $adminCredential = AdminCredential::query()->findOrFail(1);

        $this->assertSame('ADMIN', $adminCredential->login);
        $this->assertSame($passwordHash, $adminCredential->password_hash);

        $this->assertSame(
            ['owner@example.com', 'backup@example.com'],
            OrderNotificationSetting::query()->findOrFail(1)->recipient_emails,
        );
    }

    public function test_invalid_values_do_not_replace_existing_configuration(): void
    {
        $existingCredential = AdminCredential::query()->create([
            'login' => 'ADMIN',
            'password_hash' => Hash::make('Existing-admin-password'),
        ]);

        $this->artisan('app:configure-booking-alerts', [
            'recipients' => base64_encode('not-an-email'),
            'passwordHash' => base64_encode('not-a-bcrypt-hash'),
        ])->assertFailed();

        $this->assertSame(
            $existingCredential->password_hash,
            AdminCredential::query()->findOrFail($existingCredential->id)->password_hash,
        );
    }
}
