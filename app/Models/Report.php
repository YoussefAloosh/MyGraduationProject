<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_id',
        'report_type',
        'emergency_id',
        'message_id',
        'rescue_participation_id',
        'details',
        'status',
        'reported_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'reported_at'  => 'datetime',
        'processed_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(GroupChatMessage::class, 'message_id');
    }

    public function rescueParticipation(): BelongsTo
    {
        return $this->belongsTo(RescueParticipation::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}