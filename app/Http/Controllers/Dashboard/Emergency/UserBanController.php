<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\UserBan;
use Illuminate\Http\Request;

class UserBanController extends Controller
{
    public function index(Request $request)
    {
        $bans = UserBan::query()
            ->with(['user', 'banner'])
            ->when($request->is_permanent, fn($q) => $q->permanent())
            ->when($request->is_active,    fn($q) => $q->active())
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $bans->map(fn($b) => $this->formatBan($b)),
            'meta' => [
                'current_page' => $bans->currentPage(),
                'last_page'    => $bans->lastPage(),
                'total'        => $bans->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'reason'            => 'required|string|max:500',
            'ban_duration_days' => 'nullable|integer|min:1',
            'is_permanent'      => 'nullable|boolean',
        ]);

        $ban = UserBan::create([
            'user_id'      => $request->user_id,
            'reason'       => $request->reason,
            'banned_from'  => now(),
            'banned_until' => $request->is_permanent
                ? null
                : now()->addDays($request->ban_duration_days ?? 30),
            'is_permanent' => $request->is_permanent ?? false,
            'banned_by'    => auth()->id(),
        ]);

        return response()->json([
            'message' => 'User banned successfully.',
            'data'    => $this->formatBan($ban->load(['user', 'banner'])),
        ], 201);
    }

    public function lift(UserBan $userBan)
    {
        $userBan->update([
            'is_permanent' => false,
            'banned_until' => now(),
        ]);

        return response()->json(['message' => 'Ban lifted successfully.']);
    }

    private function formatBan(UserBan $b): array
    {
        $isActive = $b->is_permanent || ($b->banned_until && $b->banned_until->isFuture());

        return [
            'id'           => $b->id,
            'reason'       => $b->reason,
            'banned_from'  => $b->banned_from?->format('Y-m-d H:i'),
            'banned_until' => $b->banned_until?->format('Y-m-d H:i'),
            'is_permanent' => $b->is_permanent,
            'is_active'    => $isActive,

            'user' => [
                'id'    => $b->user->id,
                'name'  => $b->user->name,
                'email' => $b->user->email,
            ],

            'banner' => [
                'id'   => $b->banner->id,
                'name' => $b->banner->name,
            ],
        ];
    }
}
