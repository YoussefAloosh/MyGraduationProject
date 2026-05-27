<?php

namespace App\Services\Emergency;

use App\Models\Emergency;
use App\Models\EmergencyGroup;
use App\Models\GroupChatMessage;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChatService
{
    private const NORMAL_INTERVAL_HOURS = 6;
    private const MAX_CONTENT_LENGTH    = 500;

    /**
     * Send a chat message. Enforces:
     * - User must be an active permanent member.
     * - In emergency mode: no restrictions.
     * - In normal mode: 1 message per 6 hours (unless extra_messages_allowed > 0).
     */
    public function send(EmergencyGroup $group, User $user, string $content): GroupChatMessage
    {
        if (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            throw ValidationException::withMessages([
                'content' => ['Message must not exceed ' . self::MAX_CONTENT_LENGTH . ' characters.'],
            ]);
        }

        $membership = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('membership_type', 'permanent')
            ->where('membership_status', 'active')
            ->first();

        if (! $membership) {
            throw ValidationException::withMessages([
                'group' => ['You are not a permanent member of this group.'],
            ]);
        }

        // Check for active emergency in this group
        $activeEmergency = Emergency::where('target_group_id', $group->id)
            ->whereIn('status', ['new', 'in_progress', 'completed_quota'])
            ->latest()
            ->first();

        $isEmergencyMode = (bool) $activeEmergency;

        if (! $isEmergencyMode) {
            $this->enforceNormalModeLimit($group, $user, $membership);
        }

        $message = GroupChatMessage::create([
            'group_id'          => $group->id,
            'sender_id'         => $user->id,
            'emergency_id'      => $activeEmergency?->id,
            'content'           => $content,
            'sent_at'           => now(),
            'is_emergency_mode' => $isEmergencyMode,
            'is_reported_spam'  => false,
        ]);

        // Update last_activity_at
        $membership->update(['last_activity_at' => now()]);

        return $message->load('sender');
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function enforceNormalModeLimit(EmergencyGroup $group, User $user, GroupMember $membership): void
    {
        if ($membership->extra_messages_allowed > 0) {
            $membership->decrement('extra_messages_allowed');
            return;
        }

        $last = GroupChatMessage::where('group_id', $group->id)
            ->where('sender_id', $user->id)
            ->where('is_emergency_mode', false)
            ->latest('sent_at')
            ->first();

        if ($last && $last->sent_at->diffInHours(now()) < self::NORMAL_INTERVAL_HOURS) {
            $wait = self::NORMAL_INTERVAL_HOURS - (int) $last->sent_at->diffInHours(now());
            throw ValidationException::withMessages([
                'chat' => ["You must wait {$wait} more hour(s) before sending another message."],
            ]);
        }
    }
}
