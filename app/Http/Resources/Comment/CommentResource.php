<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Reaction\ReactionResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'content'    => $this->content,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            'author' => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],

            'reactions_count' => $this->whenCounted('reactions'),
            'reactions'       => ReactionResource::collection($this->whenLoaded('reactions')),
        ];
    }
}