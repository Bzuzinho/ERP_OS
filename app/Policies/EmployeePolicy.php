<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view') || $user->hasPermissionTo('hr.create') || $user->hasPermissionTo('hr.update');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->viewAny($user) && $user->organization_id === $employee->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('hr.update') && $user->organization_id === $employee->organization_id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('hr.delete') && $user->organization_id === $employee->organization_id;
    }
}
