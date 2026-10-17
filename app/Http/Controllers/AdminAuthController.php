<?php

namespace App\Http\Controllers;

use App\Services\AdminCredentialManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(private readonly AdminCredentialManager $credentials) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ((bool) $request->session()->get('admin.authenticated', false)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login', [
            'isConfigured' => $this->credentials->isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! $this->credentials->loginMatches($credentials['login'])
            || ! $this->credentials->passwordMatches($credentials['password'])) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'The admin username or password is incorrect.']);
        }

        $request->session()->regenerate();
        $configuredLogin = $this->credentials->current()['login'];
        $request->session()->put([
            'admin.authenticated' => true,
            'admin.login' => $configuredLogin,
            'admin.name' => config('admin.name'),
            'admin.credential_fingerprint' => $this->credentials->fingerprint(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('status', 'You have been signed out securely.');
    }
}
