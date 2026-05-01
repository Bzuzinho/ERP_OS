<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class ApproveLeaveRequestAction
{
    public function execute(LeaveRequest $request, array $data = []): LeaveRequest
    {
        return DB::transaction(function () use ($request, $data) {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create attendance records for the period
            $this->createAttendanceRecords($request);

            ActivityLog::create([
                'organization_id' => $request->organization_id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'subject_type' => LeaveRequest::class,
                'subject_id' => $request->id,
                'description' => "Pedido de ausência aprovado para funcionário '{$request->employee->employee_number}'",
            ]);

            return $request;
        });
    }

    private function createAttendanceRecords(LeaveRequest $request): void
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $this->mapLeaveTypeToAttendanceStatus($request->leave_type);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Skip weekends if needed
            if ($date->isWeekend()) {
                continue;
            }

            AttendanceRecord::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'organization_id' => $request->organization_id,
                    'status' => $status,
                    'source' => 'system',
                    'created_by' => auth()->id(),
                ]
            );
        }
    }

    private function mapLeaveTypeToAttendanceStatus(string $leaveType): string
    {
        return match($leaveType) {
            'vacation' => 'vacation',
            'sick_leave' => 'sick_leave',
            'justified_absence' => 'justified_absence',
            'unpaid_leave' => 'absent',
            'family_support' => 'justified_absence',
            'training' => 'training',
            default => 'absent',
        };
    }
}
