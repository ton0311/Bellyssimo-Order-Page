<?php

namespace Tests\Feature;

use App\Jobs\SendOrderEmail;
use App\Models\Order;
use App\Services\OrderEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ProductionReliabilityCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_status_succeeds_when_core_checks_are_healthy(): void
    {
        $this->artisan('app:production-status', ['--skip-backup' => true])
            ->expectsOutputToContain('Connection succeeded')
            ->assertSuccessful();
    }

    public function test_production_status_fails_when_a_failed_job_exists(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'failed-job-test',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);

        $this->artisan('app:production-status', ['--skip-backup' => true])
            ->expectsOutputToContain('1 recorded')
            ->assertFailed();
    }

    public function test_database_backup_command_fails_safely_for_unsupported_connections(): void
    {
        config(['database.default' => 'sqlite']);

        $this->artisan('app:backup-database')
            ->expectsOutput('Database backups currently support the MySQL connection only.')
            ->assertFailed();
    }

    public function test_order_email_job_records_the_resend_message_id(): void
    {
        $order = Order::factory()->create();
        $emailService = Mockery::mock(OrderEmailService::class);
        $emailService->shouldReceive('send')
            ->once()
            ->andReturn('4dd369bc-aa82-4ff3-97de-514ae3000ee0');
        $job = new SendOrderEmail(
            $order->getKey(),
            OrderEmailService::CONFIRMATION,
            null,
            'order-confirmation-'.$order->reference,
        );

        $job->handle($emailService);

        $this->assertDatabaseHas('order_email_deliveries', [
            'order_id' => $order->getKey(),
            'resend_email_id' => '4dd369bc-aa82-4ff3-97de-514ae3000ee0',
            'status' => 'sent',
        ]);
    }

    public function test_order_email_job_marks_the_delivery_as_failed_after_retries_are_exhausted(): void
    {
        $order = Order::factory()->create();
        $emailService = Mockery::mock(OrderEmailService::class);
        $emailService->shouldReceive('send')
            ->once()
            ->andThrow(new RuntimeException('Provider unavailable'));
        $job = new SendOrderEmail(
            $order->getKey(),
            OrderEmailService::CONFIRMATION,
            null,
            'order-confirmation-'.$order->reference,
        );

        try {
            $job->handle($emailService);
        } catch (RuntimeException $exception) {
            $job->failed($exception);
        }

        $this->assertDatabaseHas('order_email_deliveries', [
            'order_id' => $order->getKey(),
            'status' => 'failed',
            'failure_reason' => 'Provider unavailable',
        ]);
    }
}
