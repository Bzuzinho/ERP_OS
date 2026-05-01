<?php

namespace App\Services\Dashboard;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Reports\ReportFilterService;
use Illuminate\Support\Carbon;

class TicketKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $base = Ticket::query()->where('tickets.organization_id', $user->organization_id);
        $this->filters->applyDateRange($base, $normalized);

        $base
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('category', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['assigned_to'] ?? null, fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['source'] ?? null, fn ($q, $value) => $q->where('source', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where(function ($sub) use ($value) {
                $sub->where('reference', 'like', "%{$value}%")
                    ->orWhere('title', 'like', "%{$value}%")
                    ->orWhere('description', 'like', "%{$value}%");
            }));

        $closedStatuses = ['resolvido', 'fechado'];

        $total = (clone $base)->count();
        $open = (clone $base)->whereNotIn('status', [...$closedStatuses, 'cancelado', 'indeferido'])->count();
        $closed = (clone $base)->whereIn('status', $closedStatuses)->count();
        $urgent = (clone $base)->where('priority', 'urgent')->count();
        $overdue = (clone $base)->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString())->whereNotIn('status', $closedStatuses)->count();

        $averageCloseHours = (clone $base)
            ->whereIn('status', $closedStatuses)
            ->whereNotNull('closed_at')
            ->get(['created_at', 'closed_at'])
            ->map(fn ($ticket) => Carbon::parse($ticket->created_at)->diffInHours(Carbon::parse($ticket->closed_at)))
            ->avg();

        return [
            'total' => $total,
            'open' => $open,
            'closed' => $closed,
            'urgent' => $urgent,
            'overdue' => $overdue,
            'average_close_hours' => $averageCloseHours ? round((float) $averageCloseHours, 2) : null,
            'by_status' => (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray(),
            'by_category' => (clone $base)->selectRaw('COALESCE(category, "sem_categoria") as label, COUNT(*) as total')->groupBy('label')->pluck('total', 'label')->toArray(),
            'by_source' => (clone $base)->selectRaw('source, COUNT(*) as total')->groupBy('source')->pluck('total', 'source')->toArray(),
            'by_assignee' => (clone $base)->leftJoin('users', 'users.id', '=', 'tickets.assigned_to')->selectRaw('COALESCE(users.name, "Sem responsavel") as label, COUNT(*) as total')->groupBy('label')->pluck('total', 'label')->toArray(),
        ];
    }
}
