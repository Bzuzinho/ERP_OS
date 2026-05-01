<?php

namespace App\Services\Dashboard;

use App\Models\Task;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class TaskKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $base = Task::query()->where('tasks.organization_id', $user->organization_id);
        $this->filters->applyDateRange($base, $normalized);

        $base
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['assigned_to'] ?? null, fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"));

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'done' => (clone $base)->where('status', 'done')->count(),
            'overdue' => (clone $base)->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString())->whereNotIn('status', ['done', 'cancelled'])->count(),
            'by_assignee' => (clone $base)->leftJoin('users', 'users.id', '=', 'tasks.assigned_to')->selectRaw('COALESCE(users.name, "Sem responsavel") as label, COUNT(*) as total')->groupBy('label')->pluck('total', 'label')->toArray(),
            'by_priority' => (clone $base)->selectRaw('priority, COUNT(*) as total')->groupBy('priority')->pluck('total', 'priority')->toArray(),
            'by_status' => (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray(),
        ];
    }
}
