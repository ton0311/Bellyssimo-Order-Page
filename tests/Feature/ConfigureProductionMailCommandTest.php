<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigureProductionMailCommandTest extends TestCase
{
    public function test_valid_key_updates_the_environment_file(): void
    {
        $environmentFile = tempnam(sys_get_temp_dir(), 'bellyssimo-env-');
        $this->assertIsString($environmentFile);
        file_put_contents($environmentFile, implode(PHP_EOL, [
            'RESEND_API_KEY=',
            'MAIL_MAILER=log',
            'MAIL_FROM_ADDRESS=hello@example.com',
            'MAIL_FROM_NAME="Laravel"',
            '',
        ]));

        try {
            $this->artisan('app:mail', [
                'apiKey' => base64_encode('re_test_delivery_key'),
                'environmentFile' => $environmentFile,
            ])->assertSuccessful();

            $contents = file_get_contents($environmentFile);

            $this->assertIsString($contents);
            $this->assertStringContainsString('RESEND_API_KEY="re_test_delivery_key"', $contents);
            $this->assertStringContainsString('MAIL_MAILER="resend"', $contents);
            $this->assertStringContainsString('MAIL_FROM_ADDRESS="orders@mail.tonosbellyssimo.online"', $contents);
            $this->assertStringContainsString('MAIL_FROM_NAME="Toño\'s Bellyssimo"', $contents);
        } finally {
            unlink($environmentFile);
        }
    }

    public function test_invalid_key_does_not_update_the_environment_file(): void
    {
        $environmentFile = tempnam(sys_get_temp_dir(), 'bellyssimo-env-');
        $this->assertIsString($environmentFile);
        file_put_contents($environmentFile, "RESEND_API_KEY=\n");

        try {
            $this->artisan('app:mail', [
                'apiKey' => base64_encode('invalid-key'),
                'environmentFile' => $environmentFile,
            ])->assertFailed();

            $this->assertSame("RESEND_API_KEY=\n", file_get_contents($environmentFile));
        } finally {
            unlink($environmentFile);
        }
    }
}
