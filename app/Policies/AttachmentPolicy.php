<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use App\Support\OrganizationScope;

class AttachmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function view(User $user, Attachment $attachment): bool
    {
        if (! $attachment->attachable instanceof Ticket) {
            return false;
        }

        if (! $user->can('view', $attachment->attachable)) {
            return false;
        }

        if ($user->can('tickets.view')) {
            return true;
        }

        return $attachment->visibility === 'public';
    }

    public function create(User $user, Ticket $ticket): bool
    {
        return $user->can('view', $ticket);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if (! $attachment->attachable instanceof Ticket) {
            return false;
        }

        return $user->can('tickets.update')
            && $user->can('view', $attachment->attachable)
            && OrganizationScope::sameOrganization($attachment->attachable->organization_id, $user);
    }
}
