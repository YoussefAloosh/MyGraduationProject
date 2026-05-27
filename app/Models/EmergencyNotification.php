<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyNotification extends Model
{
    protected $fillable = [
        'emergency_id',
        'receiver_id',
        'is_read',
        'is_responded',
        'response',
        'notif_round',
        'sent_at',
        'responded_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'is_read'      => 'boolean',
        'is_responded' => 'boolean',
        'sent_at'      => 'datetime',
        'responded_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeAccepted($query)
    {
        return $query->where('response', 'accepted');
    }

    public function scopePending($query)
    {
        return $query->where('is_responded', false);
    }
}