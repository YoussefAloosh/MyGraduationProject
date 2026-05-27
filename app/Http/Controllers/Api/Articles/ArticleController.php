<?php

namespace App\Http\Controllers\Api\Articles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleCollection;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use App\Services\Articles\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function index(Request $request)
    {
        $articles = $this->articleService->paginateForApp($request);

        return new ArticleCollection($articles);
    }

    public function show(Article $article)
    {
        $this->authorize('view', $article);

        $article = $this->articleService->loadForShow($article);

        return new ArticleResource($article);
    }
}
