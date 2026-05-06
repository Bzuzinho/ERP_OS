<?php

namespace App\Policies;

use App\Models\ServiceArea;
use App\Models\User;

class ServiceAreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service_areas.view');
    }

    public function view(User $user, ServiceArea $serviceArea): bool
    {
        return $this->viewAny($user)
            && ((int) $serviceArea->organization_id === (int) $user->organization_id || $user->hasRole('super_admin'));
    }

    public function create(User $user): bool
    {
        return $user->can('service_areas.create');
    }

    public function update(User $user, ServiceArea $serviceArea): bool
    {
        return $user->can('service_areas.update')
            && ((int) $serviceArea->organization_id === (int) $user->organization_id || $user->hasRole('super_admin'));
    }

    public function delete(User $user, ServiceArea $serviceArea): bool
    {
        return $user->can('service_areas.delete')
            && ((int) $serviceArea->organization_id === (int) $user->organization_id || $user->hasRole('super_admin'));
    }

    public function manageUsers(User $user, ServiceArea $serviceArea): bool
    {
        return $user->can('service_areas.manage_users')
            && ((int) $serviceArea->organization_id === (int) $user->organization_id || $user->hasRole('super_admin'));
    }
}
