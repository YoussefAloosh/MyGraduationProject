<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Services\Emergency\SosService;
use Illuminate\Http\Request;

class SosController extends Controller
{
    public function __construct(
        private readonly SosService $sosService,
    ) {}

    public function sos(Request $request)
    {
        $data = $request->validate([
            'case_type'    => 'required|string|max:255',
            'custom_text'  => 'nullable|string|max:1000',
            'severity'     => 'nullable|in:low,medium,high,critical',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
        ]);

        $emergency = $this->sosService->create($request->user(), $data);

        return response()->json([
            'message' => 'Emergency alert sent successfully.',
            'data'    => $this->formatEmergency($emergency),
        ], 201);
    }

    public function retry(Emergency $emergency, Request $request)
    {
        $emergency = $this->sosService->retry($emergency, $request->user());

        return response()->json([
            'message' => 'Emergency alert retry sent successfully.',
            'data'    => $this->formatEmergency($emergency),
        ]);
    }

    private function formatEmergency(Emergency $emergency): array
    {
        return [
            'id'                => $emergency->id,
            'case_type'         => $emergency->case_type,
            'custom_text'       => $emergency->custom_text,
            'severity'          => $emergency->severity,
            'required_rescuers' => $emergency->required_rescuers,
            'location_lat'      => $emergency->location_lat,
            'location_lng'      => $emergency->location_lng,
            'status'            => $emergency->status,
            'retry_count'       => $emergency->retry_count,
            'created_at'        => $emergency->created_at->format('Y-m-d H:i'),
            'group'             => [
                'id'   => $emergency->group->id,
                'name' => $emergency->group->name,
            ],
        ];
    }
}
