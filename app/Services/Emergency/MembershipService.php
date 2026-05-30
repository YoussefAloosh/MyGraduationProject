<?php

namespace App\Services\Emergency;

use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function __construct(
        private GroupLocatorService $locator,
        private PendingGroupService $pendingGroups,
    ) {}

    /**
     * Join flow: official groups (inside circle) or pending queue.
     *
     * @return array<string, mixed>
     */
    public function requestJoin(User $user, float $lat, float $lng, ?int $groupId = null, bool $persistAsHome = false): array
    {
        if ($persistAsHome) {
            $user->update(['home_lat' => $lat, 'home_lng' => $lng]);
        }

        $existing = GroupMember::where('user_id', $user->id)
            ->where('membership_type', 'permanent')
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return [
                'status'   => 'already_member',
                'group_id' => $existing->group_id,
            ];
        }

        $containing = $this->locator->officialGroupsContaining($lat, $lng);

        if ($containing->isEmpty()) {
            return $this->pendingGroups->tryJoin($user, $lat, $lng);
        }

        if ($containing->count() === 1) {
            return $this->joinAsPermanent($user, $containing->first());
        }

        if ($groupId === null) {
            return [
                'status' => 'choose_group',
                'groups' => $containing->map(fn (EmergencyGroup $g) => [
                    'id'         => $g->id,
                    'name'       => $g->name,
                    'center_lat' => (float) $g->center_lat,
                    'center_lng' => (float) $g->center_lng,
                    'radius_km'  => (float) $g->radius_km,
                ])->values()->all(),
            ];
        }

        $chosen = $containing->firstWhere('id', $groupId);

        if (! $chosen) {
            return [
                'status'  => 'invalid_group',
                'message' => 'The selected group does not contain your location.',
            ];
        }

        return $this->joinAsPermanent($user, $chosen);
    }

    /**
     * @deprecated Use requestJoin() — kept for backward compatibility.
     */
    public function setHomeLocation(User $user, float $lat, float $lng): array
    {
        return $this->requestJoin($user, $lat, $lng, null, true);
    }

    public function myGroup(User $user): ?GroupMember
    {
        return GroupMember::query()
            ->where('user_id', $user->id)
            ->where('membership_type', 'permanent')
            ->where('membership_status', 'active')
            ->where('is_active', true)
            ->with('group')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function joinAsPermanent(User $user, EmergencyGroup $group): array
    {
        return DB::transaction(function () use ($user, $group) {
            GroupMember::updateOrCreate(
                ['user_id' => $user->id, 'group_id' => $group->id],
                [
                    'membership_type'   => 'permanent',
                    'membership_status' => 'active',
                    'is_active'         => true,
                    'joined_at'         => now(),
                ],
            );

            return [
                'status'      => 'joined',
                'group_id'    => $group->id,
                'group_name'  => $group->name,
                'chat_access' => true,
            ];
        });
    }
}
