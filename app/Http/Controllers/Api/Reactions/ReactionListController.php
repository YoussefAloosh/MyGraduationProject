<?php

namespace App\Http\Controllers\Api\Reactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\Reaction\ReactionResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class ReactionListController extends Controller
{
    /**
     * GET /api/articles/{article}/reactions
     * List all reactions on an article with user info.
     */
    public function forArticle(Request $request, Article $article)
    {
        $reactions = $article->reactions()
            ->with('user')
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return ReactionResource::collection($reactions)->additional([
            'meta' => [
                'current_page' => $reactions->currentPage(),
                'last_page'    => $reactions->lastPage(),
                'total'        => $reactions->total(),
                'likes_count'    => $article->reactions()->where('type', 'like')->count(),
                'dislikes_count' => $article->reactions()->where('type', 'dislike')->count(),
            ],
        ]);
    }

    /**
     * GET /api/comments/{comment}/reactions
     * List all reactions on a comment with user info.
     */
    public function forComment(Request $request, Comment $comment)
    {
        $reactions = $comment->reactions()
            ->with('user')
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return ReactionResource::collection($reactions)->additional([
            'meta' => [
                'current_page'   => $reactions->currentPage(),
                'last_page'      => $reactions->lastPage(),
                'total'          => $reactions->total(),
                'likes_count'    => $comment->reactions()->where('type', 'like')->count(),
                'dislikes_count' => $comment->reactions()->where('type', 'dislike')->count(),
            ],
        ]);
    }
}
