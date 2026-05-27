<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    protected $fillable = [
        'user_id',
        'group_id',
        'joined_at',
        'ended_at',
        'membership_status',
        'last_activity_at',
        'membership_type',
        'is_active',
        'extra_messages_allowed',
    ];

    protected $casts = [
        'joined_at'        => 'datetime',
        'ended_at'         => 'datetime',
        'last_activity_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'group_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('membership_status', 'active');
    }

    public function scopePermanent($query)
    {
        return $query->where('membership_type', 'permanent');
    }

    public function scopeTemporary($query)
    {
        return $query->where('membership_type', 'temporary');
    }

    public function scopeRecentlyActive($query)
    {
        return $query->where('last_activity_at', '>=', now()->subMonths(2));
    }
}