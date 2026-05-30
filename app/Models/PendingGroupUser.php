<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingGroupUser extends Model
{
    protected $fillable = [
        'pending_group_id',
        'user_id',
        'join_lat',
        'join_lng',
        'added_at',
    ];

    protected $casts = [
        'join_lat' => 'decimal:7',
        'join_lng' => 'decimal:7',
        'added_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function pendingGroupRequest(): BelongsTo
    {
        return $this->belongsTo(PendingGroupRequest::class, 'pending_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}