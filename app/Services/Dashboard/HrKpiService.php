<?php

namespace App\Services\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class HrKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $attendanceBase = AttendanceRecord::query()->where('organization_id', $user->organization_id);
        $this->filters->applyDateRange($attendanceBase, $normalized, 'date');
        $attendanceBase->when($normalized['employee_id'], fn ($q, $value) => $q->where('employee_id', $value));

        return [
            'active_employees' => Employee::query()->where('organization_id', $user->organization_id)->where('is_active', true)->count(),
            'present_today' => AttendanceRecord::query()->where('organization_id', $user->organization_id)->whereDate('date', now()->toDateString())->whereIn('status', ['present', 'remote', 'training', 'overtime'])->count(),
            'absent_today' => AttendanceRecord::query()->where('organization_id', $user->organization_id)->whereDate('date', now()->toDateString())->whereIn('status', ['absent', 'sick_leave', 'justified_absence', 'unjustified_absence'])->count(),
            'vacation_today' => AttendanceRecord::query()->where('organization_id', $user->organization_id)->whereDate('date', now()->toDateString())->where('status', 'vacation')->count(),
            'pending_leave_requests' => LeaveRequest::query()->where('organization_id', $user->organization_id)->where('status', 'requested')->count(),
            'attendance_by_status' => (clone $attendanceBase)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray(),
            'leave_by_type' => LeaveRequest::query()->where('organization_id', $user->organization_id)->selectRaw('leave_type, COUNT(*) as total')->groupBy('leave_type')->pluck('total', 'leave_type')->toArray(),
            'employees_by_department' => Employee::query()
                ->where('employees.organization_id', $user->organization_id)
                ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                ->selectRaw('COALESCE(departments.name, "Sem departamento") as label, COUNT(*) as total')
                ->groupBy('label')
                ->pluck('total', 'label')
                ->toArray(),
        ];
    }
}
