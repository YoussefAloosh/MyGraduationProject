<?php

namespace App\Http\Resources\Emergency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'center_lat' => $this->center_lat,
            'center_lng' => $this->center_lng,
            'radius_km'  => $this->radius_km,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            'creator' => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ],
        ];
    }
}