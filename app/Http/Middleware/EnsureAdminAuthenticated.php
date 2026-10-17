<?php

namespace App\Http\Middleware;

use App\Services\AdminCredentialManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function __construct(private readonly AdminCredentialManager $credentials) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        if ((bool) $request->session()->get('admin.authenticated', false)) {
            $currentFingerprint = $this->credentials->fingerprint();
            $sessionFingerprint = $request->session()->get('admin.credential_fingerprint');

            if (is_string($currentFingerprint)
                && (! is_string($sessionFingerprint) || $sessionFingerprint === '')) {
                $request->session()->put('admin.credential_fingerprint', $currentFingerprint);

                return $next($request);
            }

            if (is_string($currentFingerprint)
                && is_string($sessionFingerprint)
                && hash_equals($currentFingerprint, $sessionFingerprint)) {
                return $next($request);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('admin.login'));
    }
}
