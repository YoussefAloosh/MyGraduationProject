<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyFalseReport extends Model
{
    protected $table = 'emergency_false_reports';
    protected $fillable = [
        'rescue_participation_id',
        'reporter_id',
        'group_id',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────

    public function rescueParticipation(): BelongsTo
    {
        return $this->belongsTo(RescueParticipation::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmergencyGroup::class, 'group_id');
    }
}