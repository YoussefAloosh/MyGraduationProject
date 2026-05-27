<?php

namespace App\Http\Controllers\Api\Comments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // جلب تعليقات مقال
    public function index(Article $article)
    {
        $comments = $article->comments()
            ->with(['user', 'reactions'])
            ->withCount('reactions')
            ->orderByDesc('is_weighted')
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    // إضافة تعليق
    public function store(Request $request, Article $article)
    {
        $this->authorize('create', Comment::class);

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $isWeighted = auth()->user()->hasAnyRole(['trusted', 'creator', 'moderator', 'admin']);

        $comment = $article->comments()->create([
            'user_id' => auth()->id(),
            'is_weighted' => $isWeighted,
            'content' => $request->content,
        ]);

        return new CommentResource($comment->load('user'));
    }

    // حذف تعليق
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully.']);
    }
}