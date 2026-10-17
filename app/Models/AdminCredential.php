<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCredential extends Model
{
    protected $fillable = [
        'login',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];
}
