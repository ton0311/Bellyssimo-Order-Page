<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEmailDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'order_reference',
        'email_kind',
        'idempotency_key',
        'resend_email_id',
        'status',
        'latest_event_type',
        'failure_reason',
        'last_event_at',
    ];

    protected function casts(): array
    {
        return [
            'last_event_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
