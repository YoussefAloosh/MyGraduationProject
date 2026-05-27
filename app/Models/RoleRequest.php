<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleRequest extends Model
{
    protected $fillable = [
        'user_id',
        'role_type',
        'status',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'submitted_docs',
        'metadata',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'metadata'    => 'array',
    ];

    // ─── Relations ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRescuer($query)
    {
        return $query->where('role_type', 'rescuer');
    }
}