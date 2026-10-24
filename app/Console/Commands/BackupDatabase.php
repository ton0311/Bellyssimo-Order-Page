<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

#[Signature('app:backup-database {--keep-days= : Override the configured retention period} ')]
#[Description('Create a compressed MySQL database backup and prune expired local backups')]
class BackupDatabase extends Command
{
    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Database backups currently support the MySQL connection only.');

            return self::FAILURE;
        }

        if (! function_exists('gzopen')) {
            $this->error('The PHP zlib extension is required to create compressed backups.');

            return self::FAILURE;
        }

        $connection = config('database.connections.mysql');
        $backupPath = (string) config('reliability.backups.path');

        if (! is_array($connection) || $backupPath === '') {
            $this->error('The database connection or backup path is not configured.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($backupPath, 0700);

        $database = (string) ($connection['database'] ?? '');
        $username = (string) ($connection['username'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('The MySQL database name and username are required.');

            return self::FAILURE;
        }

        $timestamp = now('UTC')->format('Ymd_His');
        $backupFile = $backupPath.DIRECTORY_SEPARATOR."database_{$timestamp}.sql.gz";
        $defaultsFile = tempnam(storage_path('framework'), 'mysql-backup-');

        if (! is_string($defaultsFile)) {
            $this->error('Unable to create a temporary MySQL credentials file.');

            return self::FAILURE;
        }

        $gzipHandle = null;

        try {
            $defaults = $this->mysqlDefaultsFile($connection);

            if (file_put_contents($defaultsFile, $defaults, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to write the temporary MySQL credentials file.');
            }

            chmod($defaultsFile, 0600);
            $gzipHandle = gzopen($backupFile, 'wb9');

            if ($gzipHandle === false) {
                throw new \RuntimeException('Unable to open the backup destination.');
            }

            $process = new Process([
                (string) config('reliability.backups.mysqldump_binary', 'mysqldump'),
                "--defaults-extra-file={$defaultsFile}",
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
                $database,
            ]);
            $process->setTimeout((float) config('reliability.backups.timeout_seconds', 300));
            $stderr = '';
            $process->run(function (string $type, string $buffer) use ($gzipHandle, &$stderr): void {
                if ($type === Process::OUT) {
                    gzwrite($gzipHandle, $buffer);

                    return;
                }

                $stderr .= $buffer;
            });

            gzclose($gzipHandle);
            $gzipHandle = null;

            if (! $process->isSuccessful()) {
                throw new \RuntimeException(trim($stderr) ?: 'mysqldump exited with an error.');
            }

            if (! is_file($backupFile) || filesize($backupFile) === 0) {
                throw new \RuntimeException('The backup file is empty.');
            }

            $this->pruneExpiredBackups($backupPath);
            $this->info("Database backup created: {$backupFile}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if (is_resource($gzipHandle)) {
                gzclose($gzipHandle);
            }

            if (is_file($backupFile)) {
                unlink($backupFile);
            }

            $this->error('Database backup failed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            if (is_file($defaultsFile)) {
                unlink($defaultsFile);
            }
        }
    }

    /** @param array<string, mixed> $connection */
    private function mysqlDefaultsFile(array $connection): string
    {
        $settings = [
            'host' => (string) ($connection['host'] ?? '127.0.0.1'),
            'port' => (string) ($connection['port'] ?? '3306'),
            'user' => (string) ($connection['username'] ?? ''),
            'password' => (string) ($connection['password'] ?? ''),
        ];

        $lines = ['[client]'];

        foreach ($settings as $key => $value) {
            $escapedValue = str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', ''], $value);
            $lines[] = $key.'="'.$escapedValue.'"';
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function pruneExpiredBackups(string $backupPath): void
    {
        $option = $this->option('keep-days');
        $retentionDays = is_numeric($option)
            ? max(1, (int) $option)
            : max(1, (int) config('reliability.backups.retention_days', 14));
        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        foreach (File::glob($backupPath.DIRECTORY_SEPARATOR.'database_*.sql.gz') as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                File::delete($file);
            }
        }
    }
}
