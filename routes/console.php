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

Artisan::command('deploy:initialize {--admin-login=ADMIN} {--admin-hash=}', function (): int {
    $adminLogin = trim((string) $this->option('admin-login'));
    $adminHash = trim((string) $this->option('admin-hash'));

    if ($adminLogin === '' || $adminHash === '') {
        $this->error('Both --admin-login and --admin-hash are required.');

        return Command::FAILURE;
    }

    $environmentPath = base_path('.env');

    if (! is_file($environmentPath)) {
        $this->error('The production .env file was not found.');

        return Command::FAILURE;
    }

    if (blank(config('app.key'))) {
        Artisan::call('key:generate', ['--force' => true]);
    }

    $environment = (string) file_get_contents($environmentPath);
    $setEnvironmentValue = static function (string $contents, string $key, string $value): string {
        $line = $key."='".str_replace("'", "\\'", $value)."'";
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';

        if (preg_match($pattern, $contents) === 1) {
            return (string) preg_replace_callback($pattern, static fn (): string => $line, $contents);
        }

        return rtrim($contents).PHP_EOL.$line.PHP_EOL;
    };

    $environment = $setEnvironmentValue($environment, 'ADMIN_LOGIN', $adminLogin);
    $environment = $setEnvironmentValue($environment, 'ADMIN_PASSWORD_HASH', $adminHash);
    file_put_contents($environmentPath, $environment, LOCK_EX);

    Artisan::call('optimize:clear');
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');

    file_put_contents(
        storage_path('app/deployment-initialized.txt'),
        now()->toIso8601String().PHP_EOL,
        LOCK_EX,
    );

    $this->info('Bellyssimo production initialization completed.');

    return Command::SUCCESS;
})->purpose('Initialize a new Bellyssimo production deployment');
