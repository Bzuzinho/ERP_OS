<?php

namespace App\Services\Dashboard;

use App\Models\Event;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class EventKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $base = Event::query()->where('organization_id', $user->organization_id);
        $this->filters->applyDateRange($base, $normalized, 'start_at');

        $base
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->where('event_type', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('related_contact_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('title', 'like', "%{$value}%"));

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('start_at', now()->toDateString())->count(),
            'upcoming' => (clone $base)->where('start_at', '>=', now())->count(),
            'by_type' => (clone $base)->selectRaw('event_type, COUNT(*) as total')->groupBy('event_type')->pluck('total', 'event_type')->toArray(),
            'by_status' => (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray(),
            'linked_to_ticket' => (clone $base)->whereNotNull('related_ticket_id')->count(),
            'linked_to_contact' => (clone $base)->whereNotNull('related_contact_id')->count(),
        ];
    }
}
