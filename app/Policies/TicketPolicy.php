<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Scopes\UserOperationalScopeService;

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
        if (! $this->hasOperationalAccess($user, $ticket)) {
            return false;
        }

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
        return $user->can('tickets.update')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.assign')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.close')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.delete')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function validate(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.validate')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function submitForValidation(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.submit-validation')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function cancel(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.cancel')
            && $this->hasOperationalAccess($user, $ticket);
    }

    public function generateTasks(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.generate-tasks')
            && $this->hasOperationalAccess($user, $ticket);
    }

    private function hasOperationalAccess(User $user, Ticket $ticket): bool
    {
        if ($ticket->organization_id === null) {
            return false;
        }

        $organization = Organization::query()->find($ticket->organization_id);
        if (! $organization) {
            return false;
        }

        $scopeService = app(UserOperationalScopeService::class);

        if (! $scopeService->canAccessOrganization($user, $organization)) {
            return false;
        }

        if ($ticket->department_id !== null) {
            $department = $ticket->department()->withoutGlobalScopes()->first();
            if (! $department || ! $scopeService->canAccessDepartment($user, $department)) {
                return false;
            }
        }

        if ($ticket->service_area_id !== null) {
            $serviceArea = $ticket->serviceArea()->withoutGlobalScopes()->first();
            if (! $serviceArea || ! $scopeService->canAccessServiceArea($user, $serviceArea)) {
                return false;
            }
        }

        return true;
    }
}
