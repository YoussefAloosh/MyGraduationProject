<?php

namespace App\Http\Controllers\Api\Articles;

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

        return (new ArticleResource($article->load('user')))
            ->additional(['message' => 'Article submitted successfully. It will be reviewed before publishing.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT/PATCH /api/articles/{article}
     * Owner edits their own article — always resets status to "pending".
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = array_filter([
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => 'pending',
        ], fn ($v) => $v !== null);

        if ($request->hasFile('cover_image')) {
            // Delete old image from Cloudinary if exists
            if ($article->cover_image_public_id) {
                $this->deleteImage($article->cover_image_public_id);
            }
            $uploaded                       = $this->uploadImage($request->file('cover_image'), 'articles');
            $data['cover_image']            = $uploaded['url'];
            $data['cover_image_public_id']  = $uploaded['public_id'];
        }

        $article->update($data);

        return (new ArticleResource($article->fresh(['user'])->loadCount(['comments', 'reactions'])))
            ->additional(['message' => 'Article updated. It is now pending review again.']);
    }

    /**
     * DELETE /api/articles/{article}
     * Owner deletes their own article.
     */
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
