<?php

namespace App\Policies;

use App\Models\RecurringOperation;
use App\Models\User;

class RecurringOperationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view') || $user->can('planning.manage_recurring');
    }

    public function view(User $user, RecurringOperation $recurringOperation): bool
    {
        return $user->can('planning.view') || $user->can('planning.manage_recurring');
    }

    public function create(User $user): bool
    {
        return $user->can('planning.manage_recurring');
    }

    public function update(User $user, RecurringOperation $recurringOperation): bool
    {
        return $user->can('planning.manage_recurring');
    }

    public function delete(User $user, RecurringOperation $recurringOperation): bool
    {
        return $user->can('planning.manage_recurring');
    }
}
