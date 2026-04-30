<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('tickets.view') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->can('tickets.view')) {
            return true;
        }

        return $ticket->created_by === $user->id
            || $ticket->contact?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('tickets.create') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.update');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.assign');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.close');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.delete');
    }
}
