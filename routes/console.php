<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:hash-password', function (): int {
    $password = $this->secret('Enter the new admin password');
    $confirmation = $this->secret('Confirm the new admin password');

    if (! is_string($password) || mb_strlen($password) < 12) {
        $this->error('Use an admin password with at least 12 characters.');

        return Command::FAILURE;
    }

    if (! is_string($confirmation) || ! hash_equals($password, $confirmation)) {
        $this->error('The passwords do not match.');

        return Command::FAILURE;
    }

    $this->newLine();
    $this->info('Copy this value into ADMIN_PASSWORD_HASH in your .env file:');
    $this->line(Hash::make($password));

    return Command::SUCCESS;
})->purpose('Generate a secure password hash for the Bellyssimo admin login');
