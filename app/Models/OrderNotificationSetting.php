<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotificationSetting extends Model
{
    protected $fillable = [
        'recipient_emails',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'recipient_emails' => 'array',
        ];
    }
}
