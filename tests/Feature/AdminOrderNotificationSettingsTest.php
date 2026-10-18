<?php

namespace Tests\Feature;

use App\Models\OrderNotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderNotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_email_alert_settings(): void
    {
        $this->get(route('admin.notifications.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_see_current_booking_alert_recipients(): void
    {
        OrderNotificationSetting::query()->findOrFail(1)->update([
            'recipient_emails' => ['owner@example.com', 'backup@example.com'],
        ]);

        $this->withSession(['admin.authenticated' => true])
            ->get(route('admin.notifications.edit'))
            ->assertOk()
            ->assertViewIs('admin.order-notifications')
            ->assertSee('Booking email alerts')
            ->assertSee('owner@example.com')
            ->assertSee('backup@example.com')
            ->assertSee('Add another email address')
            ->assertSee(asset('css/admin-notifications.css'), false);
    }

    public function test_admin_can_add_a_normalized_recipient_without_duplicates(): void
    {
        $this->withSession(['admin.authenticated' => true])
            ->post(route('admin.notifications.store'), [
                'email' => ' Owner@Example.com ',
            ])
            ->assertRedirect(route('admin.notifications.edit'))
            ->assertSessionHas('status');

        $this->withSession(['admin.authenticated' => true])
            ->post(route('admin.notifications.store'), [
                'email' => 'owner@example.com',
            ])
            ->assertRedirect(route('admin.notifications.edit'));

        $this->assertSame(
            ['owner@example.com'],
            OrderNotificationSetting::query()->findOrFail(1)->recipient_emails,
        );
    }

    public function test_admin_can_remove_a_recipient_and_pause_alerts_by_removing_all(): void
    {
        OrderNotificationSetting::query()->findOrFail(1)->update([
            'recipient_emails' => ['owner@example.com'],
        ]);

        $this->withSession(['admin.authenticated' => true])
            ->delete(route('admin.notifications.destroy'), [
                'email' => 'owner@example.com',
            ])
            ->assertRedirect(route('admin.notifications.edit'))
            ->assertSessionHas('status');

        $this->assertSame(
            [],
            OrderNotificationSetting::query()->findOrFail(1)->recipient_emails,
        );

        $this->withSession(['admin.authenticated' => true])
            ->get(route('admin.notifications.edit'))
            ->assertSee('Owner alerts are paused');
    }

    public function test_invalid_recipient_is_rejected(): void
    {
        $this->withSession(['admin.authenticated' => true])
            ->from(route('admin.notifications.edit'))
            ->post(route('admin.notifications.store'), [
                'email' => 'not-an-email',
            ])
            ->assertRedirect(route('admin.notifications.edit'))
            ->assertSessionHasErrors('email');
    }
}
