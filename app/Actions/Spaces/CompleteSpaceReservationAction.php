<?php

namespace App\Actions\Spaces;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CompleteSpaceReservationAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(SpaceReservation $reservation, User $performedBy, ?string $notes = null): SpaceReservation
    {
        return DB::transaction(function () use ($reservation, $performedBy, $notes) {
            $oldStatus = $reservation->status;
            $reservation->status = 'completed';
            $reservation->save();

            $reservation->approvals()->create([
                'action' => 'completed',
                'decided_by' => $performedBy->id,
                'notes' => $notes,
                'old_status' => $oldStatus,
                'new_status' => 'completed',
            ]);

            $this->activityLogger->log(
                subject: $reservation,
                action: 'space.reservation.completed',
                user: $performedBy,
                organization: $reservation->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'completed'],
                description: 'Reserva concluida.',
            );

            return $reservation;
        });
    }
}
