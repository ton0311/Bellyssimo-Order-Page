<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'reference',
        'items',
        'quantity',
        'package_subtotal',
        'spicy_subtotal',
        'order_total',
        'fulfillment',
        'delivery_location',
        'customer_name',
        'contact_number',
        'email',
        'preferred_date',
        'preferred_time',
        'notes',
        'status',
        'payment_status',
        'payment_method',
        'submitted_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'preferred_date' => 'date',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
