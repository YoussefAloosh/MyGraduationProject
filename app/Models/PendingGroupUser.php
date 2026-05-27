<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingGroupUser extends Model
{
    protected $fillable = [
        'pending_group_id',
        'user_id',
        'added_at',
    ];

    protected $casts = [
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