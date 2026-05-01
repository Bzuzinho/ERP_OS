<?php

namespace App\Services\Reports;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Dashboard\TicketKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TicketReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly TicketKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = Ticket::query()
            ->where('organization_id', $user->organization_id)
            ->with(['assignee:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('category', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['assigned_to'], fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['source'], fn ($q, $value) => $q->where('source', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where(function ($sub) use ($value) {
                $sub->where('reference', 'like', "%{$value}%")
                    ->orWhere('title', 'like', "%{$value}%");
            }))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = Ticket::query()
            ->where('organization_id', $user->organization_id)
            ->with(['assignee:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['priority'], fn ($q, $value) => $q->where('priority', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('category', $value))
            ->when($normalized['department_id'], fn ($q, $value) => $q->where('department_id', $value))
            ->when($normalized['assigned_to'], fn ($q, $value) => $q->where('assigned_to', $value))
            ->when($normalized['source'], fn ($q, $value) => $q->where('source', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where(function ($sub) use ($value) {
                $sub->where('reference', 'like', "%{$value}%")
                    ->orWhere('title', 'like', "%{$value}%");
            }))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->limit(5000)->get()->map(fn (Ticket $ticket) => [
            'referencia' => $ticket->reference,
            'titulo' => $ticket->title,
            'categoria' => $ticket->category,
            'estado' => $ticket->status,
            'prioridade' => $ticket->priority,
            'origem' => $ticket->source,
            'responsavel' => $ticket->assignee?->name,
            'criado_em' => optional($ticket->created_at)->toDateTimeString(),
            'prazo' => optional($ticket->due_date)->toDateString(),
            'fechado_em' => optional($ticket->closed_at)->toDateTimeString(),
        ]);
    }
}
