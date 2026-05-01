<?php

namespace App\Services\Dashboard;

use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceMaintenanceRecord;
use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class SpaceKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $reservationBase = SpaceReservation::query()->where('space_reservations.organization_id', $user->organization_id);
        $this->filters->applyDateRange($reservationBase, $normalized, 'start_at');
        $reservationBase
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['space_id'], fn ($q, $value) => $q->where('space_id', $value))
            ->when($normalized['contact_id'], fn ($q, $value) => $q->where('contact_id', $value));

        return [
            'active_spaces' => Space::query()->where('organization_id', $user->organization_id)->where('is_active', true)->count(),
            'reservations_total' => (clone $reservationBase)->count(),
            'reservations_approved' => (clone $reservationBase)->where('status', 'approved')->count(),
            'reservations_pending' => (clone $reservationBase)->where('status', 'requested')->count(),
            'reservations_rejected' => (clone $reservationBase)->where('status', 'rejected')->count(),
            'occupancy_by_space' => (clone $reservationBase)
                ->leftJoin('spaces', 'spaces.id', '=', 'space_reservations.space_id')
                ->selectRaw('COALESCE(spaces.name, \'Sem espaço\') as label, COUNT(*) as total')
                ->groupBy('spaces.name')
                ->pluck('total', 'label')
                ->toArray(),
            'open_maintenance' => SpaceMaintenanceRecord::query()->where('organization_id', $user->organization_id)->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count(),
            'pending_cleaning' => SpaceCleaningRecord::query()->where('organization_id', $user->organization_id)->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count(),
        ];
    }
}
