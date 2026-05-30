<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Services\Emergency\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MembershipController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
    ) {}

    /**
     * POST /emergency/groups/join
     * Join via GPS or saved location; optional group_id when multiple matches.
     */
    public function join(Request $request)
    {
        $data = $request->validate([
            'location_source' => 'required|in:gps,saved',
            'latitude'        => 'required_if:location_source,gps|nullable|numeric|between:-90,90',
            'longitude'       => 'required_if:location_source,gps|nullable|numeric|between:-180,180',
            'group_id'        => 'nullable|integer|exists:emergency_groups,id',
        ]);

        $user = $request->user();

        if ($data['location_source'] === 'saved') {
            if ($user->home_lat === null || $user->home_lng === null) {
                throw ValidationException::withMessages([
                    'location_source' => ['No saved home location. Set home location first or use GPS.'],
                ]);
            }
            $lat = (float) $user->home_lat;
            $lng = (float) $user->home_lng;
        } else {
            $lat = (float) $data['latitude'];
            $lng = (float) $data['longitude'];
        }

        $persistAsHome = $data['location_source'] === 'saved';
        $result        = $this->membershipService->requestJoin(
            $user,
            $lat,
            $lng,
            isset($data['group_id']) ? (int) $data['group_id'] : null,
            $persistAsHome,
        );

        if (($result['status'] ?? '') === 'invalid_group') {
            throw ValidationException::withMessages([
                'group_id' => [$result['message'] ?? 'Invalid group selection.'],
            ]);
        }

        return response()->json([
            'message' => $this->messageFor($result['status']),
            'data'    => $result,
        ]);
    }

    /**
     * POST /emergency/profile/home-location
     * @deprecated Prefer POST /emergency/groups/join — saves home + joins.
     */
    public function setHomeLocation(Request $request)
    {
        $data = $request->validate([
            'home_lat' => 'required|numeric|between:-90,90',
            'home_lng' => 'required|numeric|between:-180,180',
        ]);

        $result = $this->membershipService->setHomeLocation(
            $request->user(),
            (float) $data['home_lat'],
            (float) $data['home_lng'],
        );

        return response()->json([
            'message' => $this->messageFor($result['status']),
            'data'    => $result,
        ]);
    }

    /**
     * GET /emergency/my-group
     */
    public function myGroup(Request $request)
    {
        $membership = $this->membershipService->myGroup($request->user());

        if (! $membership) {
            return response()->json([
                'message' => 'You are not a member of any emergency group yet.',
                'data'    => null,
            ]);
        }

        return response()->json([
            'data' => [
                'group' => [
                    'id'         => $membership->group->id,
                    'name'       => $membership->group->name,
                    'center_lat' => $membership->group->center_lat,
                    'center_lng' => $membership->group->center_lng,
                    'radius_km'  => $membership->group->radius_km,
                    'is_active'  => $membership->group->is_active,
                ],
                'membership' => [
                    'type'                   => $membership->membership_type,
                    'status'                 => $membership->membership_status,
                    'joined_at'              => $membership->joined_at?->format('Y-m-d H:i'),
                    'extra_messages_allowed' => $membership->extra_messages_allowed,
                ],
            ],
        ]);
    }

    private function messageFor(string $status): string
    {
        return match ($status) {
            'joined'         => 'You have been added to an emergency group.',
            'pending_queue'  => 'No official group covers your location. You have been added to the waiting queue.',
            'already_member' => 'You are already a member of an emergency group.',
            'choose_group'   => 'Multiple groups cover your location. Please choose one.',
            default          => 'Request processed.',
        };
    }
}
