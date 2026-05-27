<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyGroup;
use App\Models\GroupChatMessage;
use Illuminate\Http\Request;

class GroupChatController extends Controller
{
    public function index(Request $request, EmergencyGroup $emergencyGroup)
    {
        $messages = GroupChatMessage::query()
            ->where('group_id', $emergencyGroup->id)
            ->with(['sender', 'emergency'])
            ->when($request->is_spam, fn($q) => $q->spam())
            ->when($request->is_emergency_mode, fn($q) => $q->emergencyMode())
            ->when($request->emergency_id, fn($q) => $q->where('emergency_id', $request->emergency_id))
            ->oldest('sent_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $messages->map(fn($m) => $this->formatMessage($m)),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    public function store(Request $request, EmergencyGroup $emergencyGroup)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $message = GroupChatMessage::create([
            'group_id'          => $emergencyGroup->id,
            'sender_id'         => auth()->id(),
            'content'           => $request->content,
            'sent_at'           => now(),
            'is_emergency_mode' => false,
            'is_reported_spam'  => false,
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => $this->formatMessage($message->load(['sender', 'emergency'])),
        ]);
    }

    public function destroy(EmergencyGroup $emergencyGroup, GroupChatMessage $message)
    {
        if ($message->group_id !== $emergencyGroup->id) {
            return response()->json(['message' => 'Message not found in this group.'], 404);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully.']);
    }

    public function toggleSpam(EmergencyGroup $emergencyGroup, GroupChatMessage $message)
    {
        if ($message->group_id !== $emergencyGroup->id) {
            return response()->json(['message' => 'Message not found in this group.'], 404);
        }

        $message->update([
            'is_reported_spam' => ! $message->is_reported_spam,
        ]);

        return response()->json([
            'message'          => $message->is_reported_spam ? 'Marked as spam.' : 'Spam mark removed.',
            'is_reported_spam' => $message->is_reported_spam,
        ]);
    }

    private function formatMessage(GroupChatMessage $m): array
    {
        return [
            'id'                => $m->id,
            'content'           => $m->content,
            'sent_at'           => $m->sent_at?->format('Y-m-d H:i'),
            'is_emergency_mode' => $m->is_emergency_mode,
            'is_reported_spam'  => $m->is_reported_spam,

            'sender' => [
                'id'    => $m->sender->id,
                'name'  => $m->sender->name,
                'email' => $m->sender->email,
            ],

            'emergency' => $m->emergency ? [
                'id'        => $m->emergency->id,
                'case_type' => $m->emergency->case_type,
                'status'    => $m->emergency->status,
            ] : null,
        ];
    }
}
