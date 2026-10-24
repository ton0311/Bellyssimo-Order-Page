<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'order_reference',
        'event',
        'actor_type',
        'actor_identifier',
        'ip_hash',
        'before_values',
        'after_values',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
