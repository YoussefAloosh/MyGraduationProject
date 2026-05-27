<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'info_ready',
        'balance',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'otp_locked_until',
        'provider',
        'provider_id',
        // ─── Emergency ────────────────────────────────
        'rescue_count',
        'is_ratable',
        'spam_ban_count',
        'group_admin_revoke_count',
        'home_lat',
        'home_lng',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'info_ready'        => 'boolean',
            'otp_expires_at'    => 'datetime',
            'otp_locked_until'  => 'datetime',
            // ─── Emergency ────────────────────────────
            'is_ratable'        => 'boolean',
            'home_lat'          => 'decimal:7',
            'home_lng'          => 'decimal:7',
        ];
    }

    // ─── Relations ────────────────────────────────────────

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }
}