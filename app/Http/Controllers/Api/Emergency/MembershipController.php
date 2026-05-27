<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Services\Emergency\MembershipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
    ) {}

    /**
     * POST /emergency/profile/home-location
     * Set permanent home location → join group or enter pending queue.
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

        $messages = [
            'joined'          => 'You have been added to an emergency group.',
            'pending_queue'   => 'No group found nearby. You have been added to the waiting queue.',
            'already_member'  => 'Your home location has been updated.',
        ];

        return response()->json([
            'message' => $messages[$result['status']] ?? 'Location saved.',
            'data'    => $result,
        ]);
    }

    /**
     * GET /emergency/my-group
     * Return the user's active group membership.
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
}
