<?php

namespace App\Policies;

use App\Models\SpaceMaintenanceRecord;
use App\Models\User;

class SpaceMaintenanceRecordPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('spaces.view') || $user->can('spaces.manage_maintenance');
    }

    public function view(User $user, SpaceMaintenanceRecord $spaceMaintenanceRecord): bool
    {
        return $user->can('spaces.view') || $user->can('spaces.manage_maintenance');
    }

    public function create(User $user): bool
    {
        return $user->can('spaces.manage_maintenance');
    }

    public function update(User $user, SpaceMaintenanceRecord $spaceMaintenanceRecord): bool
    {
        return $user->can('spaces.manage_maintenance');
    }

    public function delete(User $user, SpaceMaintenanceRecord $spaceMaintenanceRecord): bool
    {
        return $user->can('spaces.manage_maintenance');
    }
}
