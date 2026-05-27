<?php

namespace App\Http\Controllers\Dashboard\Articles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;

class ArticleApprovalController extends Controller
{
    public function pending()
    {
        $this->authorize('approve', Article::class);

        $articles = Article::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->pending()
            ->latest()
            ->paginate(10);

        return ArticleResource::collection($articles);
    }

    public function approve(Article $article)
    {
        $this->authorize('approve', Article::class);

        $article->update([
            'status'       => 'approved',
            'published_at' => now(),
        ]);

        return new ArticleResource($article->load('user'));
    }

    public function reject(Article $article)
    {
        $this->authorize('approve', Article::class);

        $article->update([
            'status'       => 'rejected',
            'published_at' => null,
        ]);

        return new ArticleResource($article->load('user'));
    }
}
