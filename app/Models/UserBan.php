<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBan extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'banned_from',
        'banned_until',
        'is_permanent',
        'banned_by',
    ];

    protected $casts = [
        'banned_from'  => 'datetime',
        'banned_until' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
              ->orWhere('banned_until', '>', now());
        });
    }

    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }
}