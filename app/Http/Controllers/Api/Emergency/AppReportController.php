<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class AppReportController extends Controller
{
    /**
     * POST /emergency/reports
     *
     * Body fields:
     *   reported_id              (required) user being reported
     *   report_type              (required) false_emergency | spam_message | fake_rescue | group_admin_misconduct
     *   emergency_id             (nullable)
     *   message_id               (nullable)
     *   rescue_participation_id  (nullable)
     *   details                  (nullable)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reported_id'             => 'required|integer|exists:users,id',
            'report_type'             => 'required|in:false_emergency,spam_message,fake_rescue,group_admin_misconduct',
            'emergency_id'            => 'nullable|integer|exists:emergencies,id',
            'message_id'              => 'nullable|integer|exists:group_chat_messages,id',
            'rescue_participation_id' => 'nullable|integer|exists:rescue_participations,id',
            'details'                 => 'nullable|string|max:1000',
        ]);

        $report = Report::create([
            ...$data,
            'reporter_id' => $request->user()->id,
            'status'      => 'pending',
            'reported_at' => now(),
        ]);

        return response()->json([
            'message' => 'Report submitted. It will be reviewed by an admin.',
            'data'    => ['id' => $report->id, 'status' => $report->status],
        ], 201);
    }
}
