<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Task;
use App\Models\Event;

class EmployeeAssignmentService
{
    public function isAssignedToTask(Employee $employee, Task $task): bool
    {
        return $employee->taskAssignments()
            ->where('task_id', $task->id)
            ->where('is_active', true)
            ->exists();
    }

    public function isAssignedToEvent(Employee $employee, Event $event): bool
    {
        return $employee->eventAssignments()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->exists();
    }

    public function removeTaskAssignment(Employee $employee, Task $task): void
    {
        $employee->taskAssignments()
            ->where('task_id', $task->id)
            ->update([
                'is_active' => false,
                'removed_at' => now(),
            ]);
    }

    public function removeEventAssignment(Employee $employee, Event $event): void
    {
        $employee->eventAssignments()
            ->where('event_id', $event->id)
            ->update([
                'is_active' => false,
                'removed_at' => now(),
            ]);
    }
}
