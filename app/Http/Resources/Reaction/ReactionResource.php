<?php

namespace App\Http\Resources\Reaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'type' => $this->type,

            'user' => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}