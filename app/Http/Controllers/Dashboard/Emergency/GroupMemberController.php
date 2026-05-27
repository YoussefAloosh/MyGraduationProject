<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    public function index(Request $request, EmergencyGroup $emergencyGroup)
    {
        $members = $emergencyGroup->members()
            ->with('user')
            ->when($request->status, fn($q) => $q->where('membership_status', $request->status))
            ->when($request->type,   fn($q) => $q->where('membership_type',   $request->type))
            ->latest('joined_at')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $members->map(fn($m) => [
                'id'                     => $m->id,
                'membership_status'      => $m->membership_status,
                'membership_type'        => $m->membership_type,
                'is_active'              => $m->is_active,
                'joined_at'              => $m->joined_at?->format('Y-m-d H:i'),
                'ended_at'               => $m->ended_at?->format('Y-m-d H:i'),
                'last_activity_at'       => $m->last_activity_at?->format('Y-m-d H:i'),
                'extra_messages_allowed' => $m->extra_messages_allowed,
                'user' => [
                    'id'           => $m->user->id,
                    'name'         => $m->user->name,
                    'email'        => $m->user->email,
                    'rescue_count' => $m->user->rescue_count,
                    'home_lat'     => $m->user->home_lat,
                    'home_lng'     => $m->user->home_lng,
                ],
            ]),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page'    => $members->lastPage(),
                'total'        => $members->total(),
            ],
        ]);
    }

    public function remove(EmergencyGroup $emergencyGroup, GroupMember $member)
    {
        $member->update([
            'membership_status' => 'removed',
            'ended_at'          => now(),
            'is_active'         => false,
        ]);

        AdminActionLog::create([
            'section'        => 'emergency',
            'action_type'    => 'remove_user',
            'admin_id'       => auth()->id(),
            'target_user_id' => $member->user_id,
            'group_id'       => $emergencyGroup->id,
            'action_at'      => now(),
        ]);

        return response()->json(['message' => 'Member removed successfully.']);
    }

    public function grantMessages(Request $request, EmergencyGroup $emergencyGroup, GroupMember $member)
    {
        $request->validate([
            'extra_messages' => 'required|integer|in:1,2,3',
        ]);

        $member->update([
            'extra_messages_allowed' => $request->extra_messages,
        ]);

        AdminActionLog::create([
            'section'        => 'emergency',
            'action_type'    => 'grant_extra_messages',
            'admin_id'       => auth()->id(),
            'target_user_id' => $member->user_id,
            'group_id'       => $emergencyGroup->id,
            'extra_value'    => $request->extra_messages,
            'action_at'      => now(),
        ]);

        return response()->json(['message' => 'Extra messages granted successfully.']);
    }
}
