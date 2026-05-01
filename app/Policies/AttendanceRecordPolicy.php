<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view_attendance');
    }

    public function view(User $user, AttendanceRecord $record): bool
    {
        return $this->viewAny($user) && $user->organization_id === $record->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.create_attendance');
    }

    public function update(User $user, AttendanceRecord $record): bool
    {
        return $user->hasPermissionTo('hr.create_attendance') && $user->organization_id === $record->organization_id;
    }

    public function delete(User $user, AttendanceRecord $record): bool
    {
        return $user->hasPermissionTo('hr.create_attendance') && $user->organization_id === $record->organization_id;
    }

    public function validate(User $user, AttendanceRecord $record): bool
    {
        return $user->hasPermissionTo('hr.validate_attendance') && $user->organization_id === $record->organization_id;
    }
}
