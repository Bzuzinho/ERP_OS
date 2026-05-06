<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use App\Support\OrganizationScope;

class SpacePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('spaces.view')
            || $user->can('spaces.reserve')
            || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, Space $space): bool
    {
        if (! OrganizationScope::sameOrganization($space->organization_id, $user)) {
            return false;
        }

        if ($user->can('spaces.view')) {
            return true;
        }

        return $space->is_public && $space->is_active;
    }

    public function create(User $user): bool
    {
        return $user->can('spaces.create') || $user->can('spaces.reserve') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function update(User $user, Space $space): bool
    {
        return $user->can('spaces.update')
            && OrganizationScope::sameOrganization($space->organization_id, $user);
    }

    public function delete(User $user, Space $space): bool
    {
        return $user->can('spaces.delete')
            && OrganizationScope::sameOrganization($space->organization_id, $user);
    }
}
