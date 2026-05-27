<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use Illuminate\Http\Request;

class AdminActionLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AdminActionLog::query()
            ->with(['admin', 'targetUser', 'group'])
            ->when($request->section,     fn($q) => $q->section($request->section))
            ->when($request->action_type, fn($q) => $q->actionType($request->action_type))
            ->when($request->admin_id,    fn($q) => $q->where('admin_id',    $request->admin_id))
            ->when($request->group_id,    fn($q) => $q->where('group_id',    $request->group_id))
            ->when($request->date_from,   fn($q) => $q->whereDate('action_at', '>=', $request->date_from))
            ->when($request->date_to,     fn($q) => $q->whereDate('action_at', '<=', $request->date_to))
            ->latest('action_at')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $logs->map(fn($l) => [
                'id'          => $l->id,
                'section'     => $l->section,
                'action_type' => $l->action_type,
                'extra_value' => $l->extra_value,
                'action_at'   => $l->action_at?->format('Y-m-d H:i'),

                'admin' => [
                    'id'   => $l->admin->id,
                    'name' => $l->admin->name,
                ],

                'target_user' => [
                    'id'    => $l->targetUser->id,
                    'name'  => $l->targetUser->name,
                    'email' => $l->targetUser->email,
                ],

                'group' => $l->group ? [
                    'id'   => $l->group->id,
                    'name' => $l->group->name,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}
