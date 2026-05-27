<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\UserBan;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::query()
            ->with(['reporter', 'reported', 'processor', 'emergency'])
            ->when($request->status,      fn($q) => $q->where('status',      $request->status))
            ->when($request->report_type, fn($q) => $q->where('report_type', $request->report_type))
            ->latest('reported_at')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $reports->map(fn($r) => $this->formatReport($r)),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'total'        => $reports->total(),
            ],
        ]);
    }

    public function approve(Request $request, Report $report)
    {
        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Report already processed.'], 422);
        }

        $request->validate([
            'ban_duration_days' => 'nullable|integer|min:1',
            'is_permanent'      => 'nullable|boolean',
            'ban_reason'        => 'nullable|string|max:500',
        ]);

        $report->update([
            'status'       => 'approved',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        if ($request->ban_duration_days || $request->is_permanent) {
            UserBan::create([
                'user_id'      => $report->reported_id,
                'reason'       => $request->ban_reason ?? "Report #{$report->id} approved: {$report->report_type}",
                'banned_from'  => now(),
                'banned_until' => $request->is_permanent
                    ? null
                    : now()->addDays($request->ban_duration_days),
                'is_permanent' => $request->is_permanent ?? false,
                'banned_by'    => auth()->id(),
            ]);
        }

        return response()->json([
            'message' => 'Report approved successfully.',
            'data'    => $this->formatReport($report->fresh(['reporter', 'reported', 'processor'])),
        ]);
    }

    public function reject(Report $report)
    {
        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Report already processed.'], 422);
        }

        $report->update([
            'status'       => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Report rejected.',
            'data'    => $this->formatReport($report->fresh(['reporter', 'reported', 'processor'])),
        ]);
    }

    private function formatReport(Report $r): array
    {
        return [
            'id'           => $r->id,
            'report_type'  => $r->report_type,
            'details'      => $r->details,
            'status'       => $r->status,
            'reported_at'  => $r->reported_at?->format('Y-m-d H:i'),
            'processed_at' => $r->processed_at?->format('Y-m-d H:i'),

            'reporter' => [
                'id'    => $r->reporter->id,
                'name'  => $r->reporter->name,
                'email' => $r->reporter->email,
            ],

            'reported' => [
                'id'    => $r->reported->id,
                'name'  => $r->reported->name,
                'email' => $r->reported->email,
            ],

            'processor' => $r->processor ? [
                'id'   => $r->processor->id,
                'name' => $r->processor->name,
            ] : null,

            'emergency' => $r->emergency ? [
                'id'        => $r->emergency->id,
                'case_type' => $r->emergency->case_type,
            ] : null,
        ];
    }
}
