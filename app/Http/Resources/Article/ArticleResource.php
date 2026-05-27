<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Reaction\ReactionResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'cover_image' => $this->cover_image,
            'cover_image_public_id' => $this->cover_image_public_id,
            'status' => $this->status,
            'published_at' => $this->published_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'comments_count' => $this->whenCounted('comments'),
            'reactions_count' => $this->whenCounted('reactions'),

            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'reactions' => ReactionResource::collection($this->whenLoaded('reactions')),
        ];
    }
}