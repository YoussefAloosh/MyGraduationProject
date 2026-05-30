<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendingGroupRequest extends Model
{
    protected $fillable = [
        'center_lat',
        'center_lng',
        'radius_km',
        'nearby_users_count',
        'status',
        'submitted_to_manager_at',
    ];

    protected $casts = [
        'center_lat'              => 'decimal:7',
        'center_lng'              => 'decimal:7',
        'radius_km'               => 'decimal:2',
        'submitted_to_manager_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function pendingUsers(): HasMany
    {
        return $this->hasMany(PendingGroupUser::class, 'pending_group_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }
}