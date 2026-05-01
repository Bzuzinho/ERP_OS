<?php

namespace App\Services\Reports;

use App\Models\Event;
use App\Models\User;
use App\Services\Dashboard\EventKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EventReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly EventKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = Event::query()
            ->where('organization_id', $user->organization_id)
            ->with(['relatedContact:id,name', 'relatedTicket:id,reference'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('event_type', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('related_contact_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->orderByDesc('start_at');

        $this->filters->applyDateRange($query, $normalized, 'start_at');

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = Event::query()
            ->where('organization_id', $user->organization_id)
            ->with(['relatedContact:id,name', 'relatedTicket:id,reference'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('event_type', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('related_contact_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->orderByDesc('start_at');

        $this->filters->applyDateRange($query, $normalized, 'start_at');

        return $query->limit(5000)->get()->map(fn (Event $event) => [
            'titulo' => $event->title,
            'tipo' => $event->event_type,
            'estado' => $event->status,
            'inicio' => optional($event->start_at)->toDateTimeString(),
            'fim' => optional($event->end_at)->toDateTimeString(),
            'local' => $event->location_text,
            'contacto' => $event->relatedContact?->name,
            'ticket' => $event->relatedTicket?->reference,
        ]);
    }
}
