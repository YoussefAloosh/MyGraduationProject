<?php

namespace App\Http\Resources\Emergency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmergencyGroupCollection extends ResourceCollection
{
    public $collects = EmergencyGroupResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'per_page'     => $this->perPage(),
                'total'        => $this->total(),
            ],
        ];
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        return [];
    }
}