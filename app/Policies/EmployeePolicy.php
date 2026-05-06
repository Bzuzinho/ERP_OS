<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Support\OrganizationScope;

class EmployeePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view') || $user->hasPermissionTo('hr.create') || $user->hasPermissionTo('hr.update');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->viewAny($user) && OrganizationScope::sameOrganization($employee->organization_id, $user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('hr.update') && OrganizationScope::sameOrganization($employee->organization_id, $user);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('hr.delete') && OrganizationScope::sameOrganization($employee->organization_id, $user);
    }
}
