<?php

namespace App\Actions\Spaces;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Spaces\SpaceReservationNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class RejectSpaceReservationAction
{
    public function __construct(
        private readonly SpaceReservationNotificationService $notificationService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(SpaceReservation $reservation, User $performedBy, string $reason, ?string $notes = null): SpaceReservation
    {
        return DB::transaction(function () use ($reservation, $performedBy, $reason, $notes) {
            $oldStatus = $reservation->status;
            $reservation->status = 'rejected';
            $reservation->rejected_by = $performedBy->id;
            $reservation->rejected_at = now();
            $reservation->rejection_reason = $reason;
            $reservation->save();

            $reservation->approvals()->create([
                'action' => 'rejected',
                'decided_by' => $performedBy->id,
                'notes' => $notes,
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ]);

            $reservation->loadMissing('organization');
            $this->notificationService->notifyReservationRejected($reservation, $performedBy);

            $this->activityLogger->log(
                subject: $reservation,
                action: 'space.reservation.rejected',
                user: $performedBy,
                organization: $reservation->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'rejected', 'rejection_reason' => $reason],
                description: 'Reserva rejeitada.',
            );

            return $reservation;
        });
    }
}
