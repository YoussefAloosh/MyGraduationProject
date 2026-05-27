<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyNotification;
use App\Services\Emergency\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * GET /emergency/notifications
     * List all notifications for the authenticated user (latest first).
     */
    public function index(Request $request)
    {
        $notifications = EmergencyNotification::with(['emergency.group'])
            ->where('receiver_id', $request->user()->id)
            ->latest('sent_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id'           => $n->id,
                'is_read'      => $n->is_read,
                'is_responded' => $n->is_responded,
                'response'     => $n->response,
                'notif_round'  => $n->notif_round,
                'sent_at'      => $n->sent_at?->format('Y-m-d H:i'),
                'responded_at' => $n->responded_at?->format('Y-m-d H:i'),
                'emergency'    => $n->emergency ? [
                    'id'        => $n->emergency->id,
                    'case_type' => $n->emergency->case_type,
                    'severity'  => $n->emergency->severity,
                    'status'    => $n->emergency->status,
                    'lat'       => $n->emergency->location_lat,
                    'lng'       => $n->emergency->location_lng,
                    'group'     => $n->emergency->group
                        ? ['id' => $n->emergency->group->id, 'name' => $n->emergency->group->name]
                        : null,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * POST /emergency/notifications/{notification}/respond
     * Body: { "response": "accepted" | "rejected" }
     */
    public function respond(Request $request, EmergencyNotification $notification)
    {
        $data = $request->validate([
            'response' => 'required|in:accepted,rejected',
        ]);

        $notification = $this->notificationService->respond(
            $notification,
            $request->user(),
            $data['response'],
        );

        return response()->json([
            'message' => $data['response'] === 'accepted'
                ? 'You have accepted the rescue request.'
                : 'You have declined the rescue request.',
            'data' => [
                'id'           => $notification->id,
                'response'     => $notification->response,
                'responded_at' => $notification->responded_at?->format('Y-m-d H:i'),
                'emergency_status' => $notification->emergency?->status,
            ],
        ]);
    }
}
