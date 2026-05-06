<?php

namespace App\Policies;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Support\OrganizationScope;

class OperationalPlanPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('planning.view')
            || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, OperationalPlan $operationalPlan): bool
    {
        if (! OrganizationScope::sameOrganization($operationalPlan->organization_id, $user)) {
            return false;
        }

        if ($user->can('planning.view')) {
            return true;
        }

        if (! $user->hasAnyRole(['cidadao', 'associacao', 'empresa'])) {
            return false;
        }

        return in_array($operationalPlan->visibility, ['public', 'portal'], true)
            && in_array($operationalPlan->status, ['approved', 'scheduled', 'in_progress', 'completed'], true);
    }

    public function create(User $user): bool
    {
        return $user->can('planning.create');
    }

    public function update(User $user, OperationalPlan $operationalPlan): bool
    {
        return $user->can('planning.update')
            && OrganizationScope::sameOrganization($operationalPlan->organization_id, $user);
    }

    public function delete(User $user, OperationalPlan $operationalPlan): bool
    {
        return $user->can('planning.delete')
            && OrganizationScope::sameOrganization($operationalPlan->organization_id, $user);
    }
}
