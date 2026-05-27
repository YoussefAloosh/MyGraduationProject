<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function index(Request $request)
    {
        $emergencies = Emergency::query()
            ->with(['reporter', 'group'])
            ->withCount(['participations', 'notifications'])
            ->when($request->status,   fn($q) => $q->where('status',   $request->status))
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->when($request->group_id, fn($q) => $q->where('target_group_id', $request->group_id))
            ->when($request->is_false, fn($q) => $q->where('is_false', true))
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $emergencies->map(fn($e) => $this->formatEmergency($e)),
            'meta' => [
                'current_page' => $emergencies->currentPage(),
                'last_page'    => $emergencies->lastPage(),
                'total'        => $emergencies->total(),
            ],
        ]);
    }

    public function show(Emergency $emergency)
    {
        $emergency->load([
            'reporter',
            'group',
            'participations.user',
            'notifications.receiver',
        ])->loadCount(['participations', 'notifications']);

        return response()->json([
            'data' => [
                ...$this->formatEmergency($emergency),

                'participations' => $emergency->participations->map(fn($p) => [
                    'id'                  => $p->id,
                    'is_resolved_by_user' => $p->is_resolved_by_user,
                    'is_verified'         => $p->is_verified,
                    'accepted_at'         => $p->accepted_at?->format('Y-m-d H:i'),
                    'resolved_at'         => $p->resolved_at?->format('Y-m-d H:i'),
                    'user' => [
                        'id'           => $p->user->id,
                        'name'         => $p->user->name,
                        'email'        => $p->user->email,
                        'rescue_count' => $p->user->rescue_count,
                        'home_lat'     => $p->user->home_lat,
                        'home_lng'     => $p->user->home_lng,
                    ],
                ]),

                'notifications' => $emergency->notifications->map(fn($n) => [
                    'id'           => $n->id,
                    'response'     => $n->response,
                    'is_responded' => $n->is_responded,
                    'notif_round'  => $n->notif_round,
                    'sent_at'      => $n->sent_at?->format('Y-m-d H:i'),
                    'responded_at' => $n->responded_at?->format('Y-m-d H:i'),
                    'receiver' => [
                        'id'   => $n->receiver->id,
                        'name' => $n->receiver->name,
                    ],
                ]),
            ],
        ]);
    }

    public function markFalse(Emergency $emergency)
    {
        if ($emergency->is_false) {
            return response()->json(['message' => 'Already marked as false.'], 422);
        }

        $emergency->update([
            'is_false'  => true,
            'status'    => 'false',
            'closed_at' => now(),
        ]);

        return response()->json(['message' => 'Emergency marked as false.']);
    }

    private function formatEmergency(Emergency $e): array
    {
        return [
            'id'                   => $e->id,
            'case_type'            => $e->case_type,
            'custom_text'          => $e->custom_text,
            'severity'             => $e->severity,
            'required_rescuers'    => $e->required_rescuers,
            'location_lat'         => $e->location_lat,
            'location_lng'         => $e->location_lng,
            'status'               => $e->status,
            'is_false'             => $e->is_false,
            'retry_count'          => $e->retry_count,
            'created_at'           => $e->created_at->format('Y-m-d H:i'),
            'closed_at'            => $e->closed_at?->format('Y-m-d H:i'),
            'participations_count' => $e->participations_count ?? 0,
            'notifications_count'  => $e->notifications_count ?? 0,

            'reporter' => [
                'id'    => $e->reporter->id,
                'name'  => $e->reporter->name,
                'email' => $e->reporter->email,
            ],

            'group' => [
                'id'   => $e->group->id,
                'name' => $e->group->name,
            ],
        ];
    }
}
