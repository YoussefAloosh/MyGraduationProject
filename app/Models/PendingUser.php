<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_code',
        'verification_code_expires_at',
        'verification_attempts',
    ];

    protected $hidden = [
        'password',
        'verification_code',
    ];

    protected $casts = [
        'verification_code_expires_at' => 'datetime',
    ];
}