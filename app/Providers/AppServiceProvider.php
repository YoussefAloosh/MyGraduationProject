<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Reaction;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use App\Policies\ReactionPolicy;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Allow _method field in FormData to override HTTP verb (needed for PUT/PATCH file uploads)
        Request::enableHttpMethodParameterOverride();

        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Reaction::class, ReactionPolicy::class);
    }
}
