<?php

namespace App\Services\Spaces;

use App\Models\SpaceCleaningRecord;
use App\Models\SpaceReservation;
use App\Models\User;
use Illuminate\Support\Carbon;

class SpaceCleaningService
{
    public function ensureReservationCleaning(SpaceReservation $reservation, ?User $createdBy = null): SpaceCleaningRecord
    {
        $existing = $reservation->cleaningRecords()->latest()->first();

        if ($existing) {
            return $existing;
        }

        return SpaceCleaningRecord::create([
            'organization_id' => $reservation->organization_id,
            'space_id' => $reservation->space_id,
            'space_reservation_id' => $reservation->id,
            'status' => 'scheduled',
            'scheduled_at' => $this->suggestScheduledAt($reservation),
            'assigned_to' => null,
            'completed_by' => null,
            'notes' => 'Limpeza automatica criada apos aprovacao da reserva.',
        ]);
    }

    public function suggestScheduledAt(SpaceReservation $reservation): Carbon
    {
        return $reservation->end_at instanceof Carbon
            ? $reservation->end_at->copy()
            : Carbon::parse($reservation->end_at);
    }
}
