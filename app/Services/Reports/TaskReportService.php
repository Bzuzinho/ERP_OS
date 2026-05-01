<?php

namespace App\Services\Reports;

use App\Models\Task;
use App\Models\User;
use App\Services\Dashboard\TaskKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TaskReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly TaskKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = Task::query()
            ->where('organization_id', $user->organization_id)
            ->with(['assignee:id,name', 'ticket:id,reference'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['assigned_to'], fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = Task::query()
            ->where('organization_id', $user->organization_id)
            ->with(['assignee:id,name', 'ticket:id,reference'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['assigned_to'], fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->limit(5000)->get()->map(fn (Task $task) => [
            'titulo' => $task->title,
            'ticket' => $task->ticket?->reference,
            'estado' => $task->status,
            'prioridade' => $task->priority,
            'responsavel' => $task->assignee?->name,
            'inicio' => optional($task->start_date)->toDateString(),
            'prazo' => optional($task->due_date)->toDateString(),
            'concluida_em' => optional($task->completed_at)->toDateTimeString(),
        ]);
    }
}
