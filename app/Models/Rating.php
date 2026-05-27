<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rating extends Model
{
    protected $fillable = [
        'group_id',
        'rater_id',
        'rated_id',
        'score',
        'rated_at',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'rated_at'  => 'datetime',
        'edited_at' => 'datetime',
        'is_edited' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'group_id');
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function rated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RatingHistory::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePositive($query)
    {
        return $query->where('score', 'positive');
    }

    public function scopeNegative($query)
    {
        return $query->where('score', 'negative');
    }
}