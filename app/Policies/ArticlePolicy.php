<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Article $article): bool
    {
        if ($article->status === 'approved') return true;
        if ($user && $user->id === $article->user_id) return true;
        if ($user && $user->hasAnyRole(['admin', 'moderator', 'creator'])) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['trusted', 'creator', 'moderator', 'admin']);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->id === $article->user_id && $article->status === 'pending') {
            return true;
        }
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->id === $article->user_id) return true;
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    public function approve(User $user): bool
    {
        return $user->hasAnyRole(['creator', 'moderator', 'admin']);
    }
}