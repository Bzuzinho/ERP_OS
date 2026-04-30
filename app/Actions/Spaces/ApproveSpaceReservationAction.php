<?php

namespace App\Actions\Spaces;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Spaces\SpaceAvailabilityService;
use App\Services\Spaces\SpaceReservationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveSpaceReservationAction
{
    public function __construct(
        private readonly SpaceAvailabilityService $availabilityService,
        private readonly SpaceReservationService $reservationService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(SpaceReservation $reservation, User $performedBy, ?string $notes = null): SpaceReservation
    {
        return DB::transaction(function () use ($reservation, $performedBy, $notes) {
            $reservation->loadMissing('space', 'organization', 'event');

            if ($this->availabilityService->hasApprovedConflict(
                $reservation->space_id,
                $reservation->start_at,
                $reservation->end_at,
                $reservation->id,
            )) {
                throw new RuntimeException('Existe conflito com outra reserva aprovada para o mesmo periodo.');
            }

            $oldStatus = $reservation->status;
            $reservation->status = 'approved';
            $reservation->approved_by = $performedBy->id;
            $reservation->approved_at = now();
            $reservation->rejected_by = null;
            $reservation->rejected_at = null;
            $reservation->rejection_reason = null;
            $reservation->save();

            $reservation->approvals()->create([
                'action' => 'approved',
                'decided_by' => $performedBy->id,
                'notes' => $notes,
                'old_status' => $oldStatus,
                'new_status' => 'approved',
            ]);

            $this->reservationService->ensureReservationEvent($reservation, $performedBy);
            $this->reservationService->ensureCleaningRecord($reservation, $performedBy);

            $this->activityLogger->log(
                subject: $reservation,
                action: 'space.reservation.approved',
                user: $performedBy,
                organization: $reservation->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'approved', 'approved_by' => $performedBy->id],
                description: 'Reserva aprovada.',
            );

            return $reservation;
        });
    }
}
