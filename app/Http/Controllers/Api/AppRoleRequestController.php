<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class AppRoleRequestController extends Controller
{
    /**
     * POST /role-requests
     * Submit a role request (e.g. rescuer, publisher, seller…).
     *
     * Body:
     *   role_type      (required) rescuer | group_admin | publisher | instructor | seller | provider
     *   submitted_docs (nullable) string — URL / path of uploaded document
     *   metadata       (nullable) JSON object with extra data per role type
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'role_type'      => 'required|in:rescuer,group_admin,publisher,instructor,seller,provider',
            'submitted_docs' => 'nullable|string|max:2048',
            'metadata'       => 'nullable|array',
        ]);

        // Prevent duplicate pending request for the same role
        $existing = RoleRequest::where('user_id', $request->user()->id)
            ->where('role_type', $data['role_type'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending request for this role.',
                'data'    => ['id' => $existing->id, 'status' => $existing->status],
            ], 422);
        }

        $roleRequest = RoleRequest::create([
            'user_id'        => $request->user()->id,
            'role_type'      => $data['role_type'],
            'status'         => 'pending',
            'submitted_docs' => $data['submitted_docs'] ?? null,
            'metadata'       => $data['metadata'] ?? null,
        ]);

        return response()->json([
            'message' => 'Role request submitted. You will be notified once reviewed.',
            'data'    => [
                'id'        => $roleRequest->id,
                'role_type' => $roleRequest->role_type,
                'status'    => $roleRequest->status,
            ],
        ], 201);
    }

    /**
     * GET /role-requests/my
     * List the authenticated user's own role requests.
     */
    public function my(Request $request)
    {
        $requests = RoleRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'role_type'        => $r->role_type,
                'status'           => $r->status,
                'rejection_reason' => $r->rejection_reason,
                'reviewed_at'      => $r->reviewed_at?->format('Y-m-d H:i'),
                'created_at'       => $r->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $requests]);
    }
}
