<?php

namespace App\Services\Reports;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Dashboard\SpaceKpiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SpaceReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly SpaceKpiService $kpiService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = SpaceReservation::query()
            ->where('organization_id', $user->organization_id)
            ->with(['space:id,name', 'contact:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['space_id'], fn ($q, $value) => $q->where('space_id', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('contact_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('purpose', 'like', "%{$value}%"))
            ->orderByDesc('start_at');

        $this->filters->applyDateRange($query, $normalized, 'start_at');

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = SpaceReservation::query()
            ->where('organization_id', $user->organization_id)
            ->with(['space:id,name', 'contact:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['space_id'], fn ($q, $value) => $q->where('space_id', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('contact_id', $value))
            ->when($normalized['search'], fn ($q, $value) => $q->where('purpose', 'like', "%{$value}%"))
            ->orderByDesc('start_at');

        $this->filters->applyDateRange($query, $normalized, 'start_at');

        return $query->limit(5000)->get()->map(fn (SpaceReservation $reservation) => [
            'espaco' => $reservation->space?->name,
            'reserva' => $reservation->id,
            'contacto' => $reservation->contact?->name,
            'inicio' => optional($reservation->start_at)->toDateTimeString(),
            'fim' => optional($reservation->end_at)->toDateTimeString(),
            'estado' => $reservation->status,
            'aprovacao' => $reservation->approved_at ? 'aprovada' : ($reservation->rejected_at ? 'rejeitada' : 'pendente'),
            'finalidade' => $reservation->purpose,
        ]);
    }
}
