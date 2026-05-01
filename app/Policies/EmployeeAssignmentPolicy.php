<?php

namespace App\Policies;

use App\Models\EmployeeTaskAssignment;
use App\Models\User;

class EmployeeAssignmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.assign_employees');
    }

    public function delete(User $user, EmployeeTaskAssignment $assignment): bool
    {
        return $user->hasPermissionTo('hr.assign_employees');
    }
}
