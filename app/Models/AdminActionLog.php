<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActionLog extends Model
{
    protected $fillable = [
        'section',
        'action_type',
        'admin_id',
        'target_user_id',
        'group_id',
        'extra_value',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'group_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopeActionType($query, string $type)
    {
        return $query->where('action_type', $type);
    }
}