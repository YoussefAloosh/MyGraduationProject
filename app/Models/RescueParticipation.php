<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescueParticipation extends Model
{
    protected $fillable = [
        'emergency_id',
        'user_id',
        'is_resolved_by_user',
        'is_verified',
        'accepted_at',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved_by_user' => 'boolean',
        'is_verified'         => 'boolean',
        'accepted_at'         => 'datetime',
        'resolved_at'         => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}