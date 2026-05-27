<?php

namespace App\Http\Controllers\Dashboard\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\Article\ArticleCollection;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use App\Services\Articles\ArticleService;
use App\Traits\MangesCloudinaryFiles;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use MangesCloudinaryFiles;

    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function index(Request $request)
    {
        $articles = $this->articleService->paginateForDashboard($request);

        return new ArticleCollection($articles);
    }

    public function show(Article $article)
    {
        $this->authorize('view', $article);

        $article = $this->articleService->loadForShow($article);

        return new ArticleResource($article);
    }

    public function store(StoreArticleRequest $request)
    {
        $this->authorize('create', Article::class);

        $data            = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('cover_image')) {
            $image                         = $this->uploadImage($request->file('cover_image'), 'articles');
            $data['cover_image']           = $image['url'];
            $data['cover_image_public_id'] = $image['public_id'];
        }

        $data['status']       = auth()->user()->hasAnyRole(['creator', 'moderator', 'admin'])
            ? 'approved'
            : 'pending';
        $data['published_at'] = $data['status'] === 'approved' ? now() : null;

        $article = Article::create($data);

        return new ArticleResource($article->load('user'));
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image_public_id) {
                $this->deleteImage($article->cover_image_public_id);
            }
            $image                         = $this->uploadImage($request->file('cover_image'), 'articles');
            $data['cover_image']           = $image['url'];
            $data['cover_image_public_id'] = $image['public_id'];
        }

        $article->update($data);

        return new ArticleResource($article->load('user'));
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        if ($article->cover_image_public_id) {
            $this->deleteImage($article->cover_image_public_id);
        }

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully.']);
    }
}
