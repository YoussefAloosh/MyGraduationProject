<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;
use App\Models\Article;
use App\Models\Comment;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use App\Models\Reaction;
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

        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Reaction::class, ReactionPolicy::class);
    }
}
