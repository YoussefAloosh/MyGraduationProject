<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emergency extends Model
{
    protected $fillable = [
        'reporter_id',
        'target_group_id',
        'case_type',
        'custom_text',
        'severity',
        'required_rescuers',
        'location_lat',
        'location_lng',
        'status',
        'is_false',
        'retry_count',
        'closed_at',
    ];

    protected $casts = [
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
        'is_false'     => 'boolean',
        'closed_at'    => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'target_group_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(EmergencyNotification::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(RescueParticipation::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['new', 'in_progress', 'completed_quota']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeFalse($query)
    {
        return $query->where('is_false', true);
    }

    // ─── Helpers ──────────────────────────────────────────

    public static function requiredRescuers(string $severity): int
    {
        return match($severity) {
            'low'      => 5,
            'medium'   => 10,
            'high'     => 20,
            'critical' => 9999,
            default    => 9999,
        };
    }
}