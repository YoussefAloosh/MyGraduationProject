<?php

namespace App\Services\Emergency;

use App\Models\Emergency;
use App\Models\GroupMember;
use App\Models\RescueParticipation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmergencyResolveService
{
    /**
     * Resolve an emergency:
     * - Reporter can resolve at any time.
     * - Rescuer marks their participation as resolved;
     *   when accepted count == required_rescuers the case closes.
     */
    public function resolve(Emergency $emergency, User $user): Emergency
    {
        if (in_array($emergency->status, ['resolved', 'false'], true)) {
            throw ValidationException::withMessages([
                'emergency' => ['This emergency is already closed.'],
            ]);
        }

        return DB::transaction(function () use ($emergency, $user) {

            // Reporter closes immediately
            if ($emergency->reporter_id === $user->id) {
                $emergency->update(['status' => 'resolved', 'closed_at' => now()]);
                return $emergency->fresh();
            }

            // Rescuer marks their participation
            $participation = RescueParticipation::where('emergency_id', $emergency->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $participation) {
                throw ValidationException::withMessages([
                    'emergency' => ['You are not a rescuer for this case.'],
                ]);
            }

            $participation->update([
                'is_resolved_by_user' => true,
                'resolved_at'         => now(),
            ]);

            // Update last_activity_at on group membership
            GroupMember::where('user_id', $user->id)
                ->where('group_id', $emergency->target_group_id)
                ->where('membership_status', 'active')
                ->update(['last_activity_at' => now()]);

            // Check if enough rescuers declared resolved
            $resolvedCount = RescueParticipation::where('emergency_id', $emergency->id)
                ->where('is_resolved_by_user', true)
                ->count();

            $required = (int) $emergency->required_rescuers;

            if ($required === 9999 || $resolvedCount >= $required) {
                $emergency->update(['status' => 'resolved', 'closed_at' => now()]);
            }

            return $emergency->fresh();
        });
    }

    /**
     * GET /emergency/cases/{id} — user-facing case detail.
     */
    public function show(Emergency $emergency, User $user): array
    {
        $emergency->load(['reporter', 'group']);
        $emergency->loadCount(['participations', 'notifications']);

        $myParticipation = RescueParticipation::where('emergency_id', $emergency->id)
            ->where('user_id', $user->id)
            ->first();

        return [
            'id'                   => $emergency->id,
            'case_type'            => $emergency->case_type,
            'custom_text'          => $emergency->custom_text,
            'severity'             => $emergency->severity,
            'required_rescuers'    => $emergency->required_rescuers,
            'location_lat'         => $emergency->location_lat,
            'location_lng'         => $emergency->location_lng,
            'status'               => $emergency->status,
            'is_false'             => $emergency->is_false,
            'retry_count'          => $emergency->retry_count,
            'created_at'           => $emergency->created_at->format('Y-m-d H:i'),
            'closed_at'            => $emergency->closed_at?->format('Y-m-d H:i'),
            'participations_count' => $emergency->participations_count,
            'reporter'             => [
                'id'   => $emergency->reporter->id,
                'name' => $emergency->reporter->name,
            ],
            'group' => [
                'id'   => $emergency->group->id,
                'name' => $emergency->group->name,
            ],
            'my_participation' => $myParticipation ? [
                'is_resolved_by_user' => $myParticipation->is_resolved_by_user,
                'accepted_at'         => $myParticipation->accepted_at?->format('Y-m-d H:i'),
            ] : null,
        ];
    }
}
