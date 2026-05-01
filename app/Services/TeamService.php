<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Employee;

class TeamService
{
    public function getActiveMembers(Team $team): \Illuminate\Database\Eloquent\Collection
    {
        return $team->teamMembers()
            ->where('is_active', true)
            ->with('employee')
            ->get();
    }

    public function getInactiveMembers(Team $team): \Illuminate\Database\Eloquent\Collection
    {
        return $team->teamMembers()
            ->where('is_active', false)
            ->with('employee')
            ->get();
    }

    public function isMemberActive(Team $team, Employee $employee): bool
    {
        return $team->teamMembers()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->exists();
    }
}
