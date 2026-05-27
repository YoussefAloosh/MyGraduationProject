<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emergency\RejectRoleRequestRequest;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = RoleRequest::query()
            ->with(['user', 'reviewer'])
            ->when($request->status,    fn($q) => $q->where('status',    $request->status))
            ->when($request->role_type, fn($q) => $q->where('role_type', $request->role_type))
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $requests->map(fn($r) => $this->formatRequest($r)),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    public function show(RoleRequest $roleRequest)
    {
        $roleRequest->load(['user', 'reviewer']);

        return response()->json([
            'data' => $this->formatRequest($roleRequest),
        ]);
    }

    public function approve(RoleRequest $roleRequest)
    {
        if ($roleRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already reviewed.'], 422);
        }

        $roleRequest->update([
            'status'      => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $roleRequest->user->assignRole($roleRequest->role_type);

        return response()->json([
            'message' => 'Request approved successfully.',
            'data'    => $this->formatRequest($roleRequest->fresh(['user', 'reviewer'])),
        ]);
    }

    public function reject(RejectRoleRequestRequest $request, RoleRequest $roleRequest)
    {
        if ($roleRequest->status !== 'pending') {
            return response()->json(['message' => 'Request already reviewed.'], 422);
        }

        $roleRequest->update([
            'status'           => 'rejected',
            'reviewed_at'      => now(),
            'reviewed_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'message' => 'Request rejected.',
            'data'    => $this->formatRequest($roleRequest->fresh(['user', 'reviewer'])),
        ]);
    }

    private function formatRequest(RoleRequest $r): array
    {
        return [
            'id'               => $r->id,
            'role_type'        => $r->role_type,
            'status'           => $r->status,
            'rejection_reason' => $r->rejection_reason,
            'submitted_docs'   => $r->submitted_docs,
            'reviewed_at'      => $r->reviewed_at?->format('Y-m-d H:i'),
            'created_at'       => $r->created_at->format('Y-m-d H:i'),

            'user' => [
                'id'           => $r->user->id,
                'name'         => $r->user->name,
                'email'        => $r->user->email,
                'rescue_count' => $r->user->rescue_count,
                'roles'        => $r->user->getRoleNames(),
            ],

            'reviewer' => $r->reviewer ? [
                'id'   => $r->reviewer->id,
                'name' => $r->reviewer->name,
            ] : null,
        ];
    }
}
