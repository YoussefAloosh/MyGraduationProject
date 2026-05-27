<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupChatMessage extends Model
{
    protected $fillable = [
        'group_id',
        'sender_id',
        'emergency_id',
        'content',
        'sent_at',
        'is_emergency_mode',
        'is_reported_spam',
    ];

    protected $casts = [
        'sent_at'           => 'datetime',
        'is_emergency_mode' => 'boolean',
        'is_reported_spam'  => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'group_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeSpam($query)
    {
        return $query->where('is_reported_spam', true);
    }

    public function scopeEmergencyMode($query)
    {
        return $query->where('is_emergency_mode', true);
    }
}