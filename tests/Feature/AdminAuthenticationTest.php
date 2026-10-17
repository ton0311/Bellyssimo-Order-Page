<?php

namespace Tests\Feature;

use App\Models\AdminCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertViewIs('admin.login')
            ->assertSee('Sign in to your dashboard')
            ->assertSee('Authorized personnel only')
            ->assertSee(asset('css/admin-login.css'), false);
    }

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_with_configured_credentials(): void
    {
        config()->set('admin.name', 'Bellyssimo Owner');
        config()->set('admin.login', 'OWNER');
        config()->set('admin.password_hash', Hash::make('correct-password'));

        $this->post(route('admin.login.submit'), [
            'login' => 'owner',
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session()->get('admin.authenticated'));
        $this->assertSame('Bellyssimo Owner', session()->get('admin.name'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        config()->set('admin.login', 'OWNER');
        config()->set('admin.password_hash', Hash::make('correct-password'));

        $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), [
                'login' => 'OWNER',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('login');

        $this->assertFalse((bool) session()->get('admin.authenticated'));
    }

    public function test_admin_can_sign_out(): void
    {
        $this->withSession([
            'admin.authenticated' => true,
            'admin.name' => 'Bellyssimo Owner',
        ])->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('status', 'You have been signed out securely.');

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_mutation_routes_return_unauthenticated_json_for_guests(): void
    {
        $this->postJson(route('admin.orders.store'), [])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_admin_can_open_change_password_screen(): void
    {
        config()->set('admin.login', 'ADMIN');
        config()->set('admin.password_hash', Hash::make('admin123'));

        $this->withSession(['admin.authenticated' => true])
            ->get(route('admin.password.edit'))
            ->assertOk()
            ->assertViewIs('admin.password')
            ->assertSee('Change your admin password')
            ->assertSee('Update password')
            ->assertSee(asset('css/admin-password.css'), false);
    }

    public function test_admin_can_change_password_and_use_the_new_password(): void
    {
        config()->set('admin.login', 'ADMIN');
        config()->set('admin.password_hash', Hash::make('admin123'));

        $this->withSession(['admin.authenticated' => true])
            ->patch(route('admin.password.update'), [
                'current_password' => 'admin123',
                'password' => 'BellySecure2026',
                'password_confirmation' => 'BellySecure2026',
            ])
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHas('status');

        $credential = AdminCredential::query()->firstOrFail();

        $this->assertSame('ADMIN', $credential->login);
        $this->assertTrue(Hash::check('BellySecure2026', $credential->password_hash));
        $this->assertFalse(Hash::check('admin123', $credential->password_hash));

        $this->post(route('admin.logout'));

        $this->post(route('admin.login.submit'), [
            'login' => 'ADMIN',
            'password' => 'admin123',
        ])->assertSessionHasErrors('login');

        $this->post(route('admin.login.submit'), [
            'login' => 'ADMIN',
            'password' => 'BellySecure2026',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_current_password_does_not_change_credentials(): void
    {
        config()->set('admin.login', 'ADMIN');
        config()->set('admin.password_hash', Hash::make('admin123'));

        $this->withSession(['admin.authenticated' => true])
            ->from(route('admin.password.edit'))
            ->patch(route('admin.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'BellySecure2026',
                'password_confirmation' => 'BellySecure2026',
            ])
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseCount('admin_credentials', 0);
    }

    public function test_password_change_invalidates_an_older_admin_session(): void
    {
        config()->set('admin.login', 'ADMIN');
        config()->set('admin.password_hash', Hash::make('admin123'));
        $oldFingerprint = hash('sha256', config('admin.password_hash'));

        AdminCredential::query()->create([
            'login' => 'ADMIN',
            'password_hash' => Hash::make('BellySecure2026'),
        ]);

        $this->withSession([
            'admin.authenticated' => true,
            'admin.credential_fingerprint' => $oldFingerprint,
        ])->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->assertFalse((bool) session()->get('admin.authenticated'));
    }
}
