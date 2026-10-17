<?php

namespace App\Services;

use App\Models\AdminCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminCredentialManager
{
    /**
     * @return array{login: string|null, password_hash: string|null}
     */
    public function current(): array
    {
        $credential = AdminCredential::query()->first();

        return [
            'login' => $credential?->login ?? config('admin.login'),
            'password_hash' => $credential?->password_hash ?? config('admin.password_hash'),
        ];
    }

    public function isConfigured(): bool
    {
        $credential = $this->current();

        return filled($credential['login']) && filled($credential['password_hash']);
    }

    public function loginMatches(string $login): bool
    {
        $configuredLogin = $this->current()['login'];

        return is_string($configuredLogin)
            && hash_equals(Str::lower(trim($configuredLogin)), Str::lower(trim($login)));
    }

    public function passwordMatches(string $password): bool
    {
        $configuredHash = $this->current()['password_hash'];

        return is_string($configuredHash)
            && $configuredHash !== ''
            && Hash::check($password, $configuredHash);
    }

    public function fingerprint(): ?string
    {
        $configuredHash = $this->current()['password_hash'];

        return is_string($configuredHash) && $configuredHash !== ''
            ? hash('sha256', $configuredHash)
            : null;
    }

    public function changePassword(string $password): AdminCredential
    {
        $current = $this->current();

        return AdminCredential::query()->updateOrCreate(
            ['id' => 1],
            [
                'login' => $current['login'],
                'password_hash' => Hash::make($password),
            ],
        );
    }
}
