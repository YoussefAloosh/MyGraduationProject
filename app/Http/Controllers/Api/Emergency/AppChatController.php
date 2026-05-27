<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyGroup;
use App\Models\GroupChatMessage;
use App\Services\Emergency\ChatService;
use Illuminate\Http\Request;

class AppChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    /**
     * GET /emergency/groups/{emergencyGroup}/chat
     * Paginated messages — newest first.
     */
    public function index(Request $request, EmergencyGroup $emergencyGroup)
    {
        $messages = GroupChatMessage::with('sender')
            ->where('group_id', $emergencyGroup->id)
            ->when($request->boolean('emergency_only'), fn ($q) => $q->emergencyMode())
            ->when($request->integer('emergency_id'), fn ($q, $id) => $q->where('emergency_id', $id))
            ->latest('sent_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $messages->map(fn ($m) => [
                'id'                => $m->id,
                'content'           => $m->content,
                'sent_at'           => $m->sent_at?->format('Y-m-d H:i'),
                'is_emergency_mode' => $m->is_emergency_mode,
                'is_reported_spam'  => $m->is_reported_spam,
                'sender' => [
                    'id'   => $m->sender->id,
                    'name' => $m->sender->name,
                ],
            ]),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * POST /emergency/groups/{emergencyGroup}/chat
     * Body: { "content": "string" }
     */
    public function store(Request $request, EmergencyGroup $emergencyGroup)
    {
        $data = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $message = $this->chatService->send($emergencyGroup, $request->user(), $data['content']);

        return response()->json([
            'message' => 'Message sent.',
            'data' => [
                'id'                => $message->id,
                'content'           => $message->content,
                'sent_at'           => $message->sent_at?->format('Y-m-d H:i'),
                'is_emergency_mode' => $message->is_emergency_mode,
                'sender' => [
                    'id'   => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ],
        ], 201);
    }
}
