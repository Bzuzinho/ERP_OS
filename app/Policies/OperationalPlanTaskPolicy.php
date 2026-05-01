<?php

namespace App\Policies;

use App\Models\OperationalPlanTask;
use App\Models\User;

class OperationalPlanTaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view');
    }

    public function view(User $user, OperationalPlanTask $operationalPlanTask): bool
    {
        return $user->can('planning.view');
    }

    public function create(User $user): bool
    {
        return $user->can('planning.manage_tasks');
    }

    public function update(User $user, OperationalPlanTask $operationalPlanTask): bool
    {
        return $user->can('planning.manage_tasks');
    }

    public function delete(User $user, OperationalPlanTask $operationalPlanTask): bool
    {
        return $user->can('planning.manage_tasks');
    }
}
