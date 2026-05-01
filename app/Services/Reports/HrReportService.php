<?php

namespace App\Services\Reports;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\Dashboard\HrKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HrReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly HrKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = AttendanceRecord::query()
            ->where('attendance_records.organization_id', $user->organization_id)
            ->with(['employee.department:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['employee_id'], fn ($q, $value) => $q->where('employee_id', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $value)))
            ->orderByDesc('date');

        $this->filters->applyDateRange($query, $normalized, 'date');

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = AttendanceRecord::query()
            ->where('attendance_records.organization_id', $user->organization_id)
            ->with(['employee.department:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['employee_id'], fn ($q, $value) => $q->where('employee_id', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $value)))
            ->orderByDesc('date');

        $this->filters->applyDateRange($query, $normalized, 'date');

        return $query->limit(5000)->get()->map(fn (AttendanceRecord $record) => [
            'funcionario' => $record->employee?->employee_number,
            'departamento' => $record->employee?->department?->name,
            'status_hoje' => $record->status,
            'entrada' => $record->check_in,
            'saida' => $record->check_out,
            'minutos' => $record->worked_minutes,
            'validacao' => $record->validated_at ? 'validado' : 'pendente',
            'data' => optional($record->date)->toDateString(),
        ]);
    }
}
