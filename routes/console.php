<?php

use App\Models\AdminCredential;
use App\Models\OrderNotificationSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

Artisan::command('app:alerts {recipients} {passwordHash}', function (): int {
    $recipientPayload = base64_decode((string) $this->argument('recipients'), true);
    $passwordHash = base64_decode((string) $this->argument('passwordHash'), true);

    if (! is_string($recipientPayload) || ! is_string($passwordHash)) {
        $this->error('The configuration values must be valid base64 strings.');

        return Command::FAILURE;
    }

    $recipientEmails = array_values(array_unique(array_filter(array_map(
        static fn (string $email): string => mb_strtolower(trim($email)),
        explode(',', $recipientPayload),
    ))));

    if ($recipientEmails === []) {
        $this->error('At least one recipient email address is required.');

        return Command::FAILURE;
    }

    foreach ($recipientEmails as $recipientEmail) {
        if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) === false) {
            $this->error("Invalid recipient email address: {$recipientEmail}");

            return Command::FAILURE;
        }
    }

    if (password_get_info($passwordHash)['algoName'] !== 'bcrypt') {
        $this->error('The admin password hash must use bcrypt.');

        return Command::FAILURE;
    }

    if (Artisan::call('migrate', ['--force' => true]) !== Command::SUCCESS) {
        $this->error(Artisan::output());

        return Command::FAILURE;
    }

    $adminLogin = config('admin.login');
    $adminLogin = is_string($adminLogin) && filled($adminLogin) ? $adminLogin : 'ADMIN';

    DB::transaction(function () use ($adminLogin, $passwordHash, $recipientEmails): void {
        AdminCredential::query()->updateOrCreate(
            ['id' => 1],
            [
                'login' => $adminLogin,
                'password_hash' => $passwordHash,
            ],
        );

        OrderNotificationSetting::query()->updateOrCreate(
            ['id' => 1],
            ['recipient_emails' => $recipientEmails],
        );
    });

    Artisan::call('optimize:clear');

    $this->info('Booking alerts and the admin login are configured.');

    return Command::SUCCESS;
})->purpose('Apply production booking-alert recipients and an admin password hash');

Artisan::command('app:mail {apiKey} {environmentFile?}', function (): int {
    $apiKey = base64_decode((string) $this->argument('apiKey'), true);

    if (! is_string($apiKey) || ! str_starts_with($apiKey, 're_') || mb_strlen($apiKey) < 10) {
        $this->error('The Resend API key is invalid.');

        return Command::FAILURE;
    }

    $environmentFile = $this->argument('environmentFile');
    $environmentFile = is_string($environmentFile) && $environmentFile !== ''
        ? $environmentFile
        : app()->environmentFilePath();

    if (! is_file($environmentFile) || ! is_writable($environmentFile)) {
        $this->error('The environment file is not writable.');

        return Command::FAILURE;
    }

    $contents = file_get_contents($environmentFile);

    if (! is_string($contents)) {
        $this->error('The environment file could not be read.');

        return Command::FAILURE;
    }

    $settings = [
        'RESEND_API_KEY' => $apiKey,
        'MAIL_MAILER' => 'resend',
        'MAIL_FROM_ADDRESS' => 'orders@mail.tonosbellyssimo.online',
        'MAIL_FROM_NAME' => "Toño's Bellyssimo",
    ];

    foreach ($settings as $key => $value) {
        $line = $key.'="'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        $contents = preg_match($pattern, $contents) === 1
            ? preg_replace_callback($pattern, static fn (): string => $line, $contents)
            : rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }

    if (! is_string($contents) || file_put_contents($environmentFile, $contents, LOCK_EX) === false) {
        $this->error('The environment file could not be updated.');

        return Command::FAILURE;
    }

    Artisan::call('config:clear');

    $this->info('Resend delivery is configured.');

    return Command::SUCCESS;
})->purpose('Apply the production Resend key and verified sender settings');
