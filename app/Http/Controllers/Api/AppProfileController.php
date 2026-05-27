<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Reaction\ReactionResource;
use Illuminate\Http\Request;

class AppProfileController extends Controller
{
    /**
     * GET /api/profile/reactions
     * All reactions the authenticated user has made.
     */
    public function reactions(Request $request)
    {
        $reactions = $request->user()
            ->reactions()
            ->with('reactionable')
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $reactions->map(fn ($r) => [
                'id'               => $r->id,
                'type'             => $r->type,
                'created_at'       => $r->created_at->format('Y-m-d H:i'),
                'reactionable_type'=> class_basename($r->reactionable_type),
                'reactionable_id'  => $r->reactionable_id,
                'reactionable'     => $r->reactionable ? [
                    'id'    => $r->reactionable->id,
                    'title' => $r->reactionable->title  // Article
                           ?? $r->reactionable->content // Comment (first 80 chars)
                           ?? null,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $reactions->currentPage(),
                'last_page'    => $reactions->lastPage(),
                'total'        => $reactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/profile/comments
     * All comments the authenticated user has written.
     */
    public function comments(Request $request)
    {
        $comments = $request->user()
            ->comments()
            ->with(['user', 'commentable'])
            ->withCount('reactions')
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $comments->map(fn ($c) => [
                'id'              => $c->id,
                'content'         => $c->content,
                'reactions_count' => $c->reactions_count,
                'created_at'      => $c->created_at->format('Y-m-d H:i'),
                'on'              => $c->commentable ? [
                    'type'  => class_basename($c->commentable_type),
                    'id'    => $c->commentable->id,
                    'title' => $c->commentable->title ?? null,
                ] : null,
            ]),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page'    => $comments->lastPage(),
                'total'        => $comments->total(),
            ],
        ]);
    }
}
