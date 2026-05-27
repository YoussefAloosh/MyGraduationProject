<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingHistory extends Model
{
    protected $table = 'rating_history';
    protected $fillable = [
        'rating_id',
        'old_score',
        'new_score',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }
}