<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyGroup extends Model
{
    protected $fillable = [
        'name',
        'center_lat',
        'center_lng',
        'radius_km',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'center_lat' => 'decimal:7',
        'center_lng' => 'decimal:7',
        'radius_km'  => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id')
            ->where('is_active', true)
            ->where('membership_status', 'active');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371;
        $latDelta    = deg2rad($lat - $this->center_lat);
        $lngDelta    = deg2rad($lng - $this->center_lng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($this->center_lat))
            * cos(deg2rad($lat))
            * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function containsLocation(float $lat, float $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_km;
    }
}