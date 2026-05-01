<?php

namespace App\Services\Reports;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Dashboard\PlanningKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PlanningReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly PlanningKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = OperationalPlan::query()
            ->where('organization_id', $user->organization_id)
            ->with(['owner:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['plan_type'], fn ($q, $value) => $q->where('plan_type', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['user_id'], fn ($q, $value) => $q->where('owner_user_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->latest();

        $this->filters->applyDateRange($query, $normalized, 'start_date');

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = OperationalPlan::query()
            ->where('organization_id', $user->organization_id)
            ->with(['owner:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['plan_type'], fn ($q, $value) => $q->where('plan_type', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['user_id'], fn ($q, $value) => $q->where('owner_user_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->latest();

        $this->filters->applyDateRange($query, $normalized, 'start_date');

        return $query->limit(5000)->get()->map(fn (OperationalPlan $plan) => [
            'plano' => $plan->title,
            'tipo' => $plan->plan_type,
            'estado' => $plan->status,
            'visibilidade' => $plan->visibility,
            'inicio' => optional($plan->start_date)->toDateString(),
            'fim' => optional($plan->end_date)->toDateString(),
            'responsavel' => $plan->owner?->name,
            'progresso' => $plan->progress_percent,
        ]);
    }
}
