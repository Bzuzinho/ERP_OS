<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view_leave') || $user->hasPermissionTo('hr.create_leave') || $user->hasPermissionTo('hr.approve_leave');
    }

    public function view(User $user, LeaveRequest $request): bool
    {
        return $this->viewAny($user) && $user->organization_id === $request->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.create_leave');
    }

    public function update(User $user, LeaveRequest $request): bool
    {
        return $user->hasPermissionTo('hr.create_leave') && $user->organization_id === $request->organization_id;
    }

    public function approve(User $user, LeaveRequest $request): bool
    {
        return $user->hasPermissionTo('hr.approve_leave') && $user->organization_id === $request->organization_id;
    }

    public function reject(User $user, LeaveRequest $request): bool
    {
        return $user->hasPermissionTo('hr.reject_leave') && $user->organization_id === $request->organization_id;
    }

    public function delete(User $user, LeaveRequest $request): bool
    {
        return $user->hasPermissionTo('hr.create_leave') && $user->organization_id === $request->organization_id;
    }
}
