<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Support\OrganizationScope;

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
        if (! $comment->commentable instanceof Ticket) {
            return false;
        }

        if (! OrganizationScope::sameOrganization($comment->commentable->organization_id, $user)) {
            return false;
        }

        if ($user->can('tickets.update') && $user->can('view', $comment->commentable)) {
            return true;
        }

        return $comment->user_id === $user->id;
    }
}
