<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('roles.view');
    }

    public function view(User $authUser, Role $role): bool
    {
        return $authUser->can('roles.view');
    }

    public function update(User $authUser, Role $role): bool
    {
        if (! $authUser->can('roles.update')) {
            return false;
        }

        // Only super_admin can edit the super_admin role
        if ($role->name === 'super_admin' && ! $authUser->hasRole('super_admin')) {
            return false;
        }

        return true;
    }
}
