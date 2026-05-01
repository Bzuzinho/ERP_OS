<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Team;

class RemoveEmployeeFromTeamAction
{
    public function execute(Team $team, Employee $employee): void
    {
        $team->teamMembers()
            ->where('employee_id', $employee->id)
            ->update([
                'is_active' => false,
                'left_at' => now()->toDateString(),
            ]);

        ActivityLog::create([
            'organization_id' => $team->organization_id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'subject_type' => Team::class,
            'subject_id' => $team->id,
            'description' => "Funcionário '{$employee->employee_number}' removido da equipa '{$team->name}'",
        ]);
    }
}
