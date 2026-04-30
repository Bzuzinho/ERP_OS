<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function view(User $user, Comment $comment): bool
    {
        if (! $comment->commentable instanceof Ticket) {
            return false;
        }

        if (! $user->can('view', $comment->commentable)) {
            return false;
        }

        if ($user->can('tickets.view')) {
            return true;
        }

        return $comment->visibility === 'public';
    }

    public function create(User $user, Ticket $ticket): bool
    {
        return $user->can('view', $ticket);
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->can('tickets.update')) {
            return true;
        }

        return $comment->user_id === $user->id;
    }
}
