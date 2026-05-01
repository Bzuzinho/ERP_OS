<?php

namespace App\Services\Dashboard;

use App\Models\OperationalPlan;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class PlanningKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $base = OperationalPlan::query()->where('organization_id', $user->organization_id);
        $this->filters->applyDateRange($base, $normalized, 'start_date');

        $base
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['plan_type'], fn ($q, $value) => $q->where('plan_type', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['user_id'], fn ($q, $value) => $q->where('owner_user_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"));

        return [
            'total' => (clone $base)->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'pending_approval' => (clone $base)->where('status', 'pending_approval')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'average_progress' => round((float) ((clone $base)->avg('progress_percent') ?? 0), 2),
            'active_recurring' => RecurringOperation::query()->where('organization_id', $user->organization_id)->where('status', 'active')->count(),
            'failed_recurring_runs' => RecurringOperationRun::query()->whereHas('recurringOperation', fn ($q) => $q->where('organization_id', $user->organization_id))->where('status', 'failed')->count(),
        ];
    }
}
