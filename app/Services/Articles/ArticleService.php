<?php

namespace App\Services\Articles;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ArticleService
{
    public function paginateForApp(Request $request): LengthAwarePaginator
    {
        return Article::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->approved()
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->per_page ?? 10);
    }

    public function paginateForDashboard(Request $request): LengthAwarePaginator
    {
        return Article::query()
            ->with('user')
            ->withCount(['comments', 'reactions'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->per_page ?? 10);
    }

    public function loadForShow(Article $article): Article
    {
        return $article
            ->load(['user', 'comments.user', 'reactions.user'])
            ->loadCount(['comments', 'reactions']);
    }
}
