<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('events.view') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->can('events.view')) {
            return true;
        }

        if ($event->visibility === 'internal') {
            return false;
        }

        if ($event->visibility === 'public') {
            return true;
        }

        $contactUserId = $event->relatedContact?->user_id;
        $participantUser = $event->participants()->where('user_id', $user->id)->exists();
        $participantContact = $event->participants()->whereHas('contact', fn ($query) => $query->where('user_id', $user->id))->exists();

        return $contactUserId === $user->id || $participantUser || $participantContact;
    }

    public function create(User $user): bool
    {
        return $user->can('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can('events.update');
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can('events.delete');
    }
}
