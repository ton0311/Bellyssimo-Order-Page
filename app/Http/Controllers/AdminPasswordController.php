<?php

namespace App\Http\Controllers;

use App\Services\AdminCredentialManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPasswordController extends Controller
{
    public function __construct(private readonly AdminCredentialManager $credentials) {}

    public function edit(): View
    {
        return view('admin.password', [
            'adminLogin' => $this->credentials->current()['login'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'different:current_password',
                Password::min(8)->letters()->numbers(),
                'confirmed',
            ],
        ]);

        if (! $this->credentials->passwordMatches($validated['current_password'])) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $this->credentials->changePassword($validated['password']);
        $request->session()->regenerate();
        $request->session()->put([
            'admin.authenticated' => true,
            'admin.login' => $this->credentials->current()['login'],
            'admin.name' => config('admin.name'),
            'admin.credential_fingerprint' => $this->credentials->fingerprint(),
        ]);

        return redirect()
            ->route('admin.password.edit')
            ->with('status', 'Your admin password has been changed. Other admin sessions have been signed out.');
    }
}
