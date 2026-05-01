<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeTaskAssignment;
use App\Models\Task;

class AssignEmployeeToTaskAction
{
    public function execute(Employee $employee, Task $task, array $data = []): EmployeeTaskAssignment
    {
        $assignment = EmployeeTaskAssignment::create([
            'employee_id' => $employee->id,
            'task_id' => $task->id,
            'assigned_by' => auth()->id(),
            'role' => $data['role'] ?? null,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Optionally set task assigned_to if empty and employee has user
        if (empty($task->assigned_to) && $employee->user_id) {
            $task->update(['assigned_to' => $employee->user_id]);
        }

        ActivityLog::create([
            'organization_id' => $task->organization_id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'description' => "Funcionário '{$employee->employee_number}' atribuído à tarefa '{$task->title}'",
        ]);

        return $assignment;
    }
}
