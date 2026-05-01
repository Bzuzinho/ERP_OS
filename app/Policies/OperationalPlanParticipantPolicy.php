<?php

namespace App\Policies;

use App\Models\OperationalPlanParticipant;
use App\Models\User;

class OperationalPlanParticipantPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view');
    }

    public function view(User $user, OperationalPlanParticipant $operationalPlanParticipant): bool
    {
        return $user->can('planning.view');
    }

    public function create(User $user): bool
    {
        return $user->can('planning.update');
    }

    public function update(User $user, OperationalPlanParticipant $operationalPlanParticipant): bool
    {
        return $user->can('planning.update');
    }

    public function delete(User $user, OperationalPlanParticipant $operationalPlanParticipant): bool
    {
        return $user->can('planning.update');
    }
}
