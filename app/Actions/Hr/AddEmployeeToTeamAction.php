<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Team;

class AddEmployeeToTeamAction
{
    public function execute(Team $team, Employee $employee, array $data = []): void
    {
        // Check if already active member
        $existingMember = $team->teamMembers()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->first();

        if ($existingMember) {
            return; // Already an active member
        }

        // Check if inactive member, reactivate
        $inactiveMember = $team->teamMembers()
            ->where('employee_id', $employee->id)
            ->where('is_active', false)
            ->first();

        if ($inactiveMember) {
            $inactiveMember->update([
                'is_active' => true,
                'left_at' => null,
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
            ]);
        } else {
            $team->teamMembers()->create([
                'employee_id' => $employee->id,
                'role' => $data['role'] ?? null,
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
                'is_active' => true,
            ]);
        }

        ActivityLog::create([
            'organization_id' => $team->organization_id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Team::class,
            'subject_id' => $team->id,
            'description' => "Funcionário '{$employee->employee_number}' adicionado à equipa '{$team->name}'",
        ]);
    }
}
