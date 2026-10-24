<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

#[Signature('app:production-status {--skip-backup : Do not fail when no recent backup is present}')]
#[Description('Check the database, queue failures, email failures, and backup freshness')]
class ProductionStatus extends Command
{
    public function handle(): int
    {
        $checks = [];

        try {
            DB::select('SELECT 1');
            $checks['Database'] = ['OK', 'Connection succeeded'];
        } catch (Throwable $exception) {
            $checks['Database'] = ['FAIL', $exception->getMessage()];
        }

        if (Schema::hasTable('failed_jobs')) {
            $failedJobs = DB::table('failed_jobs')->count();
            $threshold = max(1, (int) config('reliability.failed_job_threshold', 1));
            $checks['Failed jobs'] = [
                $failedJobs >= $threshold ? 'FAIL' : 'OK',
                "{$failedJobs} recorded",
            ];
        } else {
            $checks['Failed jobs'] = ['FAIL', 'failed_jobs table is missing'];
        }

        if (Schema::hasTable('jobs')) {
            $checks['Pending jobs'] = ['OK', DB::table('jobs')->count().' queued'];
        }

        if (Schema::hasTable('order_email_deliveries')) {
            $emailFailures = DB::table('order_email_deliveries')
                ->whereIn('status', ['failed', 'bounced', 'complained', 'suppressed'])
                ->where('updated_at', '>=', now()->subDay())
                ->count();
            $checks['Email delivery'] = [
                $emailFailures > 0 ? 'FAIL' : 'OK',
                "{$emailFailures} failure events in the last 24 hours",
            ];
        }

        if (! $this->option('skip-backup')) {
            $checks['Database backup'] = $this->backupCheck();
        }

        $this->table(
            ['Check', 'Status', 'Details'],
            collect($checks)->map(
                fn (array $result, string $name): array => [$name, $result[0], $result[1]],
            )->values()->all(),
        );

        $failedChecks = collect($checks)->filter(fn (array $result): bool => $result[0] === 'FAIL');

        if ($failedChecks->isNotEmpty()) {
            Log::critical('Production reliability check failed.', [
                'checks' => $failedChecks->all(),
            ]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** @return array{string, string} */
    private function backupCheck(): array
    {
        $backupPath = (string) config('reliability.backups.path');
        $files = File::glob($backupPath.DIRECTORY_SEPARATOR.'database_*.sql.gz');

        if ($files === []) {
            return ['FAIL', 'No database backup was found'];
        }

        $latestModifiedAt = collect($files)
            ->map(fn (string $file): int => (int) filemtime($file))
            ->max();
        $ageHours = (int) now()->diffInHours(CarbonImmutable::createFromTimestamp($latestModifiedAt));
        $maxAgeHours = max(1, (int) config('reliability.backups.max_age_hours', 26));

        return [
            $ageHours > $maxAgeHours ? 'FAIL' : 'OK',
            "Latest backup is {$ageHours} hours old (maximum {$maxAgeHours})",
        ];
    }
}
