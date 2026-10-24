<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResendWebhookEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'webhook_event_id',
        'resend_email_id',
        'event_type',
        'occurred_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
