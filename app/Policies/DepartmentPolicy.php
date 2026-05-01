<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view') || $user->hasPermissionTo('hr.manage_departments');
    }

    public function view(User $user, Department $department): bool
    {
        return $this->viewAny($user) && $user->organization_id === $department->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.manage_departments');
    }

    public function update(User $user, Department $department): bool
    {
        return $this->create($user) && $user->organization_id === $department->organization_id;
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->create($user) && $user->organization_id === $department->organization_id;
    }
}
