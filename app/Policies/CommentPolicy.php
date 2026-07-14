<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function hide(User $user, Comment $comment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isAdminDaerah()) {
            return false;
        }

        $regionId = $comment->complaint()->value('region_id');

        return $regionId !== null
            && $user->regionalAssignments()->where('region_id', $regionId)->exists();
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $this->hide($user, $comment);
    }
}
