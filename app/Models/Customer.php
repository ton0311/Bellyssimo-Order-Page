<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'contact_number',
        'notes',
        'last_order_at',
    ];

    protected function casts(): array
    {
        return [
            'last_order_at' => 'datetime',
        ];
    }
}
