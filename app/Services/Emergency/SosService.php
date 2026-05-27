<?php

namespace App\Services\Emergency;

use App\Models\Emergency;
use App\Models\EmergencyNotification;
use App\Models\User;
use App\Models\UserBan;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SosService
{
    private const MAX_RETRIES = 3;

    private const MEMBERS_PER_ROUND = 5;

    public function __construct(
        private readonly GroupLocatorService $groupLocator,
    ) {}

    public function create(User $reporter, array $data): Emergency
    {
        $this->ensureUserCanTriggerSos($reporter);

        $group = $this->groupLocator->locate(
            (float) $data['location_lat'],
            (float) $data['location_lng'],
        );

        if (! $group) {
            throw ValidationException::withMessages([
                'location' => ['No active emergency group covers this location.'],
            ]);
        }

        $severity = $data['severity'] ?? 'critical';

        $emergency = Emergency::create([
            'reporter_id'       => $reporter->id,
            'target_group_id'   => $group->id,
            'case_type'         => $data['case_type'],
            'custom_text'       => $data['custom_text'] ?? null,
            'severity'          => $severity,
            'required_rescuers' => Emergency::requiredRescuers($severity),
            'location_lat'      => $data['location_lat'],
            'location_lng'      => $data['location_lng'],
            'status'            => 'new',
        ]);

        $this->notifyMembers($emergency, notifRound: 1);

        return $emergency->load(['reporter', 'group']);
    }

    public function retry(Emergency $emergency, User $user): Emergency
    {
        $this->ensureUserCanTriggerSos($user);

        if ($emergency->reporter_id !== $user->id) {
            throw ValidationException::withMessages([
                'emergency' => ['You can only retry your own emergency cases.'],
            ]);
        }

        if (in_array($emergency->status, ['resolved', 'false'], true) || $emergency->is_false) {
            throw ValidationException::withMessages([
                'emergency' => ['This emergency case is already closed.'],
            ]);
        }

        if ($emergency->retry_count >= self::MAX_RETRIES) {
            throw ValidationException::withMessages([
                'emergency' => ['Maximum retry attempts reached.'],
            ]);
        }

        $emergency->increment('retry_count');
        $notifRound = $emergency->retry_count + 1;

        $this->notifyMembers($emergency->fresh(), notifRound: $notifRound);

        return $emergency->fresh(['reporter', 'group']);
    }

    private function ensureUserCanTriggerSos(User $user): void
    {
        $isBanned = UserBan::query()
            ->where('user_id', $user->id)
            ->active()
            ->exists();

        if ($isBanned) {
            throw ValidationException::withMessages([
                'user' => ['You are banned from triggering emergency alerts.'],
            ]);
        }
    }

    private function notifyMembers(Emergency $emergency, int $notifRound): void
    {
        $alreadyNotified = EmergencyNotification::query()
            ->where('emergency_id', $emergency->id)
            ->pluck('receiver_id');

        $members = $emergency->group
            ->activeMembers()
            ->with('user')
            ->where('user_id', '!=', $emergency->reporter_id)
            ->whereNotIn('user_id', $alreadyNotified)
            ->get()
            ->pluck('user')
            ->filter()
            ->values();

        if ($members->isEmpty()) {
            return;
        }

        $this->selectMembersForRound($members, $notifRound)
            ->each(fn (User $member) => EmergencyNotification::create([
                'emergency_id' => $emergency->id,
                'receiver_id'  => $member->id,
                'is_read'      => false,
                'is_responded' => false,
                'notif_round'  => $notifRound,
                'sent_at'      => now(),
            ]));
    }

    private function selectMembersForRound(Collection $members, int $notifRound): Collection
    {
        $offset = ($notifRound - 1) * self::MEMBERS_PER_ROUND;

        return $members->slice($offset, self::MEMBERS_PER_ROUND)->values();
    }
}
