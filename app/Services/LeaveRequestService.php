<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveRequestService
{
    public function calculateTotalDays(Carbon $startDate, Carbon $endDate): int
    {
        return $endDate->diffInDays($startDate) + 1;
    }

    public function validateNoOverlap(int $employeeId, Carbon $startDate, Carbon $endDate): void
    {
        $overlap = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', ['requested', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlap) {
            throw new \Exception('Existe um pedido de ausência sobreposto para este funcionário no período especificado.');
        }
    }

    public function mapLeaveTypeToAttendanceStatus(string $leaveType): string
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
