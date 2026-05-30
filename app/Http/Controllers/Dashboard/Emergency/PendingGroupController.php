<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use App\Models\PendingGroupRequest;
use App\Services\Emergency\PendingGroupService;
use Illuminate\Http\Request;

class PendingGroupController extends Controller
{
    public function index(Request $request)
    {
        $requests = PendingGroupRequest::query()
            ->with('pendingUsers.user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $requests->map(fn($r) => [
                'id'                      => $r->id,
                'center_lat'              => $r->center_lat,
                'center_lng'              => $r->center_lng,
                'radius_km'               => $r->radius_km ?? 5,
                'nearby_users_count'      => $r->nearby_users_count,
                'status'                  => $r->status,
                'submitted_to_manager_at' => $r->submitted_to_manager_at?->format('Y-m-d H:i'),
                'created_at'              => $r->created_at->format('Y-m-d H:i'),
                'users' => $r->pendingUsers->map(fn($pu) => [
                    'id'       => $pu->user->id,
                    'name'     => $pu->user->name,
                    'email'    => $pu->user->email,
                    'join_lat' => $pu->join_lat ?? $pu->user->home_lat,
                    'join_lng' => $pu->join_lng ?? $pu->user->home_lng,
                ]),
            ]),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    public function approve(Request $request, PendingGroupRequest $pendingGroupRequest)
    {
        if ($pendingGroupRequest->status === 'completed') {
            return response()->json(['message' => 'This pending request is already closed.'], 422);
        }

        if ($pendingGroupRequest->nearby_users_count < PendingGroupService::MIN_MEMBERS_FOR_SUBMISSION) {
            return response()->json([
                'message' => 'Cannot approve: at least '.PendingGroupService::MIN_MEMBERS_FOR_SUBMISSION.' members required.',
            ], 422);
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'radius_km' => 'nullable|numeric|min:1|max:50',
        ]);

        $group = EmergencyGroup::create([
            'name'       => $request->name,
            'center_lat' => $pendingGroupRequest->center_lat,
            'center_lng' => $pendingGroupRequest->center_lng,
            'radius_km'  => $request->radius_km ?? $pendingGroupRequest->radius_km ?? 5,
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        foreach ($pendingGroupRequest->pendingUsers as $pendingUser) {
            GroupMember::create([
                'user_id'           => $pendingUser->user_id,
                'group_id'          => $group->id,
                'membership_type'   => 'permanent',
                'membership_status' => 'active',
                'joined_at'         => now(),
                'is_active'         => true,
            ]);
        }

        $pendingGroupRequest->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Group created successfully.',
            'group'   => [
                'id'   => $group->id,
                'name' => $group->name,
            ],
        ]);
    }

    public function reject(PendingGroupRequest $pendingGroupRequest)
    {
        $pendingGroupRequest->update(['status' => 'completed']);

        return response()->json(['message' => 'Request rejected.']);
    }
}
