<?php

namespace App\Http\Controllers\Api\Reactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reaction\StoreReactionRequest;
use App\Http\Resources\Reaction\ReactionResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Reaction;

class ReactionController extends Controller
{
    public function store(StoreReactionRequest $request)
    {
        $this->authorize('create', Reaction::class);

        $morphMap = [
            'article' => Article::class,
            'comment' => Comment::class,
        ];

        $modelClass = $morphMap[$request->reactionable_type];
        $model      = $modelClass::findOrFail($request->reactionable_id);

        // إذا في تفاعل موجود نحدثو أو نمسحو
        $existing = $model->reactions()
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            if ($existing->type === $request->type) {
                $existing->delete();
                return response()->json(['message' => 'Reaction removed.']);
            }
            $existing->update(['type' => $request->type]);
            return new ReactionResource($existing->load('user'));
        }

        $reaction = $model->reactions()->create([
            'user_id' => auth()->id(),
            'type'    => $request->type,
        ]);

        return new ReactionResource($reaction->load('user'));
    }
}