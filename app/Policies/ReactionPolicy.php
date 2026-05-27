<?php

namespace App\Policies;

use App\Models\Reaction;
use App\Models\User;

class ReactionPolicy
{
    // مين يقدر يتفاعل
    public function create(User $user): bool
    {
        // كل المستخدمين المسجلين
        return $user->hasAnyRole(['member', 'trusted', 'creator', 'moderator', 'admin']);
    }

    // مين يقدر يحذف تفاعلو
    public function delete(User $user, Reaction $reaction): bool
    {
        // صاحب التفاعل بس أو الأدمن
        if ($user->id === $reaction->user_id) return true;
        return $user->hasAnyRole(['admin', 'moderator']);
    }
}