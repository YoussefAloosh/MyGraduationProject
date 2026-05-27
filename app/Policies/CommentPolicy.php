<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    // مين يقدر يعلق
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['member', 'trusted', 'creator', 'moderator', 'admin']);
    }

    // مين يقدر يعدل تعليق
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    // مين يقدر يحذف تعليق
    public function delete(User $user, Comment $comment): bool
    {
        if ($user->id === $comment->user_id) return true;
        return $user->hasAnyRole(['admin', 'moderator']);
    }
}