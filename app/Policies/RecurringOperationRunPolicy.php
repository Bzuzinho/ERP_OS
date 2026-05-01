<?php

namespace App\Policies;

use App\Models\RecurringOperationRun;
use App\Models\User;

class RecurringOperationRunPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view') || $user->can('planning.manage_recurring');
    }

    public function view(User $user, RecurringOperationRun $recurringOperationRun): bool
    {
        return $user->can('planning.view') || $user->can('planning.manage_recurring');
    }

    public function create(User $user): bool
    {
        return $user->can('planning.manage_recurring');
    }

    public function update(User $user, RecurringOperationRun $recurringOperationRun): bool
    {
        return $user->can('planning.execute_recurring') || $user->can('planning.manage_recurring');
    }

    public function delete(User $user, RecurringOperationRun $recurringOperationRun): bool
    {
        return $user->can('planning.manage_recurring');
    }
}
