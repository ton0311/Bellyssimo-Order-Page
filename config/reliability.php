<?php

return [
    'backups' => [
        'path' => env('DATABASE_BACKUP_PATH') ?: storage_path('app/private/backups'),
        'retention_days' => (int) env('DATABASE_BACKUP_RETENTION_DAYS', 14),
        'max_age_hours' => (int) env('DATABASE_BACKUP_MAX_AGE_HOURS', 26),
        'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'timeout_seconds' => (int) env('DATABASE_BACKUP_TIMEOUT_SECONDS', 300),
    ],
    'failed_job_threshold' => (int) env('FAILED_JOB_ALERT_THRESHOLD', 1),
];
