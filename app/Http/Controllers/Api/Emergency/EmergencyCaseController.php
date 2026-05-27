<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Services\Emergency\EmergencyResolveService;
use Illuminate\Http\Request;

class EmergencyCaseController extends Controller
{
    public function __construct(
        private readonly EmergencyResolveService $resolveService,
    ) {}

    /**
     * GET /emergency/cases/{emergency}
     * View case detail for the authenticated user.
     */
    public function show(Emergency $emergency, Request $request)
    {
        return response()->json([
            'data' => $this->resolveService->show($emergency, $request->user()),
        ]);
    }

    /**
     * POST /emergency/cases/{emergency}/resolve
     * Reporter or rescuer closes the case.
     */
    public function resolve(Emergency $emergency, Request $request)
    {
        $updated = $this->resolveService->resolve($emergency, $request->user());

        return response()->json([
            'message' => $updated->status === 'resolved'
                ? 'Emergency case has been resolved.'
                : 'Your resolution has been recorded.',
            'data' => [
                'id'         => $updated->id,
                'status'     => $updated->status,
                'closed_at'  => $updated->closed_at?->format('Y-m-d H:i'),
            ],
        ]);
    }
}
