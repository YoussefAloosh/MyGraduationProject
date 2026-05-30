<?php

namespace App\Services\Emergency;

use App\Models\PendingGroupRequest;
use App\Models\PendingGroupUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PendingGroupService
{
    public const MIN_MEMBERS_FOR_SUBMISSION = 5;

    /**
     * No official group contains the user — try pending circles, then create new.
     *
     * @return array<string, mixed>
     */
    public function tryJoin(User $user, float $lat, float $lng): array
    {
        $candidates = $this->pendingGroupsContaining($lat, $lng);

        foreach ($candidates as $pending) {
            if ($this->userAlreadyInPending($pending->id, $user->id)) {
                continue;
            }

            if ($this->attemptAddToPending($pending, $user, $lat, $lng)) {
                $pending->refresh();

                return [
                    'status'               => 'pending_queue',
                    'pending_group_id'     => $pending->id,
                    'nearby_users_count'   => $pending->nearby_users_count,
                    'pending_status'       => $pending->status,
                    'center_lat'           => (float) $pending->center_lat,
                    'center_lng'           => (float) $pending->center_lng,
                    'radius_km'            => (float) ($pending->radius_km ?? GeoCircleHelper::MIN_RADIUS_KM),
                    'chat_access'          => false,
                ];
            }
        }

        return $this->createNewPending($user, $lat, $lng);
    }

    /**
     * Pending requests (pending|submitted) whose circle contains the point.
     */
    public function pendingGroupsContaining(float $lat, float $lng): Collection
    {
        return PendingGroupRequest::query()
            ->whereIn('status', ['pending', 'submitted'])
            ->with('pendingUsers')
            ->get()
            ->filter(function (PendingGroupRequest $pending) use ($lat, $lng) {
                $radius = (float) ($pending->radius_km ?? GeoCircleHelper::MIN_RADIUS_KM);

                return GeoCircleHelper::pointInsideCircle(
                    $lat,
                    $lng,
                    (float) $pending->center_lat,
                    (float) $pending->center_lng,
                    $radius,
                );
            })
            ->sortBy(fn (PendingGroupRequest $p) => GeoCircleHelper::haversineKm(
                $lat,
                $lng,
                (float) $p->center_lat,
                (float) $p->center_lng,
            ))
            ->values();
    }

    private function userAlreadyInPending(int $pendingId, int $userId): bool
    {
        return PendingGroupUser::query()
            ->where('pending_group_id', $pendingId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Simulate add → recalc center/radius → validate all inside; persist on success.
     */
    private function attemptAddToPending(PendingGroupRequest $pending, User $user, float $lat, float $lng): bool
    {
        $points = $this->collectPoints($pending);
        $points[] = ['lat' => $lat, 'lng' => $lng];

        $center = GeoCircleHelper::centroid($points);
        $radius = GeoCircleHelper::radiusFromPoints($center['lat'], $center['lng'], $points);

        if (! GeoCircleHelper::allPointsInside($center['lat'], $center['lng'], $radius, $points)) {
            return false;
        }

        return DB::transaction(function () use ($pending, $user, $lat, $lng, $center, $radius, $points) {
            PendingGroupUser::create([
                'pending_group_id' => $pending->id,
                'user_id'          => $user->id,
                'join_lat'         => $lat,
                'join_lng'         => $lng,
                'added_at'         => now(),
            ]);

            $count = count($points);

            $pending->update([
                'center_lat'         => $center['lat'],
                'center_lng'         => $center['lng'],
                'radius_km'          => $radius,
                'nearby_users_count' => $count,
                'status'             => $count >= self::MIN_MEMBERS_FOR_SUBMISSION ? 'submitted' : 'pending',
                'submitted_to_manager_at' => $count >= self::MIN_MEMBERS_FOR_SUBMISSION && ! $pending->submitted_to_manager_at
                    ? now()
                    : $pending->submitted_to_manager_at,
            ]);

            return true;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function createNewPending(User $user, float $lat, float $lng): array
    {
        $radius = GeoCircleHelper::MIN_RADIUS_KM;

        $pending = PendingGroupRequest::create([
            'center_lat'          => $lat,
            'center_lng'          => $lng,
            'radius_km'           => $radius,
            'nearby_users_count'  => 1,
            'status'              => 'pending',
        ]);

        PendingGroupUser::create([
            'pending_group_id' => $pending->id,
            'user_id'          => $user->id,
            'join_lat'         => $lat,
            'join_lng'         => $lng,
            'added_at'         => now(),
        ]);

        return [
            'status'             => 'pending_queue',
            'pending_group_id'   => $pending->id,
            'nearby_users_count' => 1,
            'pending_status'     => 'pending',
            'center_lat'         => $lat,
            'center_lng'         => $lng,
            'radius_km'          => $radius,
            'chat_access'        => false,
        ];
    }

    /**
     * @return array<int, array{lat: float, lng: float}>
     */
    private function collectPoints(PendingGroupRequest $pending): array
    {
        $pending->loadMissing('pendingUsers.user');

        return $pending->pendingUsers->map(function (PendingGroupUser $row) {
            return [
                'lat' => (float) ($row->join_lat ?? $row->user->home_lat ?? 0),
                'lng' => (float) ($row->join_lng ?? $row->user->home_lng ?? 0),
            ];
        })->all();
    }
}
