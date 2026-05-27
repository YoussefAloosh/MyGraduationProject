<?php

namespace App\Services\Emergency;

use App\Models\Emergency;
use App\Models\EmergencyNotification;
use App\Models\GroupMember;
use App\Models\RescueParticipation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    /**
     * Respond to an incoming notification (accepted | rejected).
     * - accepted: create RescueParticipation, increment rescue_count,
     *             update emergency status if quota met.
     * - rejected: mark responded, send next member in queue.
     */
    public function respond(EmergencyNotification $notification, User $user, string $response): EmergencyNotification
    {
        if ($notification->receiver_id !== $user->id) {
            throw ValidationException::withMessages([
                'notification' => ['This notification does not belong to you.'],
            ]);
        }

        if ($notification->is_responded) {
            throw ValidationException::withMessages([
                'notification' => ['You have already responded to this notification.'],
            ]);
        }

        return DB::transaction(function () use ($notification, $user, $response) {

            $notification->update([
                'is_responded' => true,
                'is_read'      => true,
                'response'     => $response,
                'responded_at' => now(),
                'read_at'      => now(),
            ]);

            if ($response === 'accepted') {
                $this->handleAccepted($notification, $user);
            } else {
                $this->sendNextNotification($notification);
            }

            return $notification->fresh('emergency');
        });
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function handleAccepted(EmergencyNotification $notification, User $user): void
    {
        $emergency = $notification->emergency;

        RescueParticipation::firstOrCreate(
            ['emergency_id' => $emergency->id, 'user_id' => $user->id],
            ['accepted_at' => now(), 'is_resolved_by_user' => false, 'is_verified' => false]
        );

        $user->increment('rescue_count');

        // Update last_activity_at on the group membership
        GroupMember::where('user_id', $user->id)
            ->where('group_id', $emergency->target_group_id)
            ->where('membership_status', 'active')
            ->update(['last_activity_at' => now()]);

        // Check quota
        $accepted = RescueParticipation::where('emergency_id', $emergency->id)->count();
        if ($emergency->status === 'new' || $emergency->status === 'in_progress') {
            if ($emergency->required_rescuers !== 9999 && $accepted >= $emergency->required_rescuers) {
                $emergency->update(['status' => 'completed_quota']);
            } elseif ($emergency->status === 'new') {
                $emergency->update(['status' => 'in_progress']);
            }
        }
    }

    private function sendNextNotification(EmergencyNotification $notification): void
    {
        $emergency = $notification->emergency;

        if (in_array($emergency->status, ['resolved', 'false'], true)) {
            return;
        }

        $notified = EmergencyNotification::where('emergency_id', $emergency->id)
            ->pluck('receiver_id');

        $next = GroupMember::active()
            ->where('group_id', $emergency->target_group_id)
            ->where('user_id', '!=', $emergency->reporter_id)
            ->whereNotIn('user_id', $notified)
            ->first();

        if ($next) {
            EmergencyNotification::create([
                'emergency_id' => $emergency->id,
                'receiver_id'  => $next->user_id,
                'is_read'      => false,
                'is_responded' => false,
                'notif_round'  => $notification->notif_round,
                'sent_at'      => now(),
            ]);
        }
    }
}
