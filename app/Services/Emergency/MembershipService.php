<?php

namespace App\Services\Emergency;

use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use App\Models\PendingGroupRequest;
use App\Models\PendingGroupUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function __construct(
        private readonly GroupLocatorService $groupLocator,
    ) {}

    /**
     * Set the user's permanent home location, then assign them to a group
     * (permanent member) or add them to the nearest pending queue.
     */
    public function setHomeLocation(User $user, float $lat, float $lng): array
    {
        $user->update(['home_lat' => $lat, 'home_lng' => $lng]);

        return DB::transaction(function () use ($user, $lat, $lng) {

            // If already an active permanent member of any group, just update location.
            $existing = GroupMember::where('user_id', $user->id)
                ->where('membership_type', 'permanent')
                ->where('membership_status', 'active')
                ->first();

            if ($existing) {
                return ['status' => 'already_member', 'group_id' => $existing->group_id];
            }

            $group = $this->groupLocator->locate($lat, $lng);

            if ($group) {
                $this->joinAsPermament($user, $group);
                return ['status' => 'joined', 'group_id' => $group->id, 'group_name' => $group->name];
            }

            // No group found — add to pending queue.
            $this->addToPendingQueue($user, $lat, $lng);
            return ['status' => 'pending_queue'];
        });
    }

    /**
     * Return the active group membership for the user.
     */
    public function myGroup(User $user): ?GroupMember
    {
        return GroupMember::with(['group'])
            ->where('user_id', $user->id)
            ->where('membership_status', 'active')
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    public function joinAsPermament(User $user, EmergencyGroup $group): GroupMember
    {
        return GroupMember::updateOrCreate(
            ['user_id' => $user->id, 'group_id' => $group->id],
            [
                'membership_type'   => 'permanent',
                'membership_status' => 'active',
                'is_active'         => true,
                'joined_at'         => now(),
                'ended_at'          => null,
            ]
        );
    }

    private function addToPendingQueue(User $user, float $lat, float $lng): void
    {
        // Find a nearby pending request within 5km.
        $existing = PendingGroupRequest::whereIn('status', ['pending', 'submitted'])
            ->get()
            ->first(function (PendingGroupRequest $req) use ($lat, $lng) {
                $dist = $this->haversine($lat, $lng, (float) $req->center_lat, (float) $req->center_lng);
                return $dist <= 5.0;
            });

        if ($existing) {
            // Update centroid and count.
            $count     = $existing->nearby_users_count;
            $newCount  = $count + 1;
            $newLat    = (($existing->center_lat * $count) + $lat) / $newCount;
            $newLng    = (($existing->center_lng * $count) + $lng) / $newCount;

            $existing->update([
                'center_lat'          => $newLat,
                'center_lng'          => $newLng,
                'nearby_users_count'  => $newCount,
                'status'              => $newCount >= 3 ? 'submitted' : 'pending',
                'submitted_to_manager_at' => $newCount >= 3 && ! $existing->submitted_to_manager_at
                    ? now() : $existing->submitted_to_manager_at,
            ]);

            PendingGroupUser::firstOrCreate([
                'pending_group_id' => $existing->id,
                'user_id'          => $user->id,
            ], ['added_at' => now()]);

        } else {
            $pending = PendingGroupRequest::create([
                'center_lat'          => $lat,
                'center_lng'          => $lng,
                'nearby_users_count'  => 1,
                'status'              => 'pending',
            ]);

            PendingGroupUser::create([
                'pending_group_id' => $pending->id,
                'user_id'          => $user->id,
                'added_at'         => now(),
            ]);
        }
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R  = 6371;
        $dL = deg2rad($lat2 - $lat1);
        $dG = deg2rad($lng2 - $lng1);
        $a  = sin($dL / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dG / 2) ** 2;

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
