<?php

namespace App\Http\Controllers\Api\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
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

    /**
     * POST /api/articles
     * Trusted users submit new articles — always stored as "pending" until approved.
     */
    public function store(StoreArticleRequest $request)
    {
        $this->authorize('create', Article::class);

        $data = [
            'user_id' => $request->user()->id,
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => 'pending',
        ];

        if ($request->hasFile('cover_image')) {
            $uploaded               = $this->uploadImage($request->file('cover_image'), 'articles');
            $data['cover_image']           = $uploaded['url'];
            $data['cover_image_public_id'] = $uploaded['public_id'];
        }

        $article = Article::create($data);

        return response()->json([
            'message' => 'Article submitted successfully. It will be reviewed before publishing.',
            'data'    => [
                'id'     => $article->id,
                'title'  => $article->title,
                'status' => $article->status,
                'slug'   => $article->slug,
            ],
        ], 201);
    }
}
