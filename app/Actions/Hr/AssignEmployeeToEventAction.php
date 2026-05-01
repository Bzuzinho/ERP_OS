<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeEventAssignment;
use App\Models\Event;
use App\Models\EventParticipant;

class AssignEmployeeToEventAction
{
    public function execute(Employee $employee, Event $event, array $data = []): EmployeeEventAssignment
    {
        $assignment = EmployeeEventAssignment::create([
            'employee_id' => $employee->id,
            'event_id' => $event->id,
            'assigned_by' => auth()->id(),
            'role' => $data['role'] ?? null,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Optionally create event participant if employee has user
        if ($employee->user_id) {
            EventParticipant::firstOrCreate([
                'event_id' => $event->id,
                'user_id' => $employee->user_id,
            ], [
                'role' => $data['role'] ?? null,
                'attendance_status' => 'invited',
            ]);
        }

        ActivityLog::create([
            'organization_id' => $event->organization_id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Event::class,
            'subject_id' => $event->id,
            'description' => "Funcionário '{$employee->employee_number}' atribuído ao evento '{$event->title}'",
        ]);

        return $assignment;
    }
}
