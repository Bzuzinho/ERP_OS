<?php

namespace App\Policies;

use App\Models\OperationalPlanResource;
use App\Models\User;

class OperationalPlanResourcePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view');
    }

    public function view(User $user, OperationalPlanResource $operationalPlanResource): bool
    {
        return $user->can('planning.view');
    }

    public function create(User $user): bool
    {
        return $user->can('planning.manage_resources');
    }

    public function update(User $user, OperationalPlanResource $operationalPlanResource): bool
    {
        return $user->can('planning.manage_resources');
    }

    public function delete(User $user, OperationalPlanResource $operationalPlanResource): bool
    {
        return $user->can('planning.manage_resources');
    }
}
