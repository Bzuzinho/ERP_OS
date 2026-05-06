<?php

namespace App\Actions\Spaces;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Spaces\SpaceReservationNotificationService;
use App\Services\Spaces\SpaceReservationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CancelSpaceReservationAction
{
    public function __construct(
        private readonly SpaceReservationService $reservationService,
        private readonly SpaceReservationNotificationService $notificationService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(SpaceReservation $reservation, User $performedBy, ?string $reason = null, ?string $notes = null): SpaceReservation
    {
        return DB::transaction(function () use ($reservation, $performedBy, $reason, $notes) {
            $oldStatus = $reservation->status;
            $reservation->status = 'cancelled';
            $reservation->cancelled_by = $performedBy->id;
            $reservation->cancelled_at = now();
            $reservation->cancellation_reason = $reason;
            $reservation->save();

            $reservation->approvals()->create([
                'action' => 'cancelled',
                'decided_by' => $performedBy->id,
                'notes' => $notes,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
            ]);

            $reservation->loadMissing('event', 'organization');
            
            // Cancel associated event and tasks
            $this->reservationService->cancelReservationEvent($reservation, $performedBy);
            $this->reservationService->cancelReservationTasks($reservation, $performedBy);

            // Send notification
            $this->notificationService->notifyReservationCancelled($reservation, $performedBy);

            $this->activityLogger->log(
                subject: $reservation,
                action: 'space.reservation.cancelled',
                user: $performedBy,
                organization: $reservation->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'cancelled', 'cancellation_reason' => $reason],
                description: 'Reserva cancelada.',
            );

            return $reservation;
        });
    }
}
