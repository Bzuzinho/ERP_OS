<?php

namespace App\Actions\Spaces;

use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Spaces\SpaceAvailabilityService;
use App\Services\Spaces\SpaceReservationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateSpaceReservationAction
{
    public function __construct(
        private readonly SpaceAvailabilityService $availabilityService,
        private readonly SpaceReservationService $reservationService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $creator, array $data, bool $isPortal = false): SpaceReservation
    {
        return DB::transaction(function () use ($creator, $data, $isPortal) {
            /** @var Space $space */
            $space = Space::query()->findOrFail($data['space_id']);

            $availability = $this->availabilityService->isAvailable(
                spaceId: $space->id,
                startAt: $data['start_at'],
                endAt: $data['end_at'],
            );

            if (! $availability['available']) {
                throw new RuntimeException('O espaco ja tem uma reserva aprovada em conflito no periodo indicado.');
            }

            $status = $space->requires_approval ? 'requested' : 'approved';

            $reservation = SpaceReservation::create([
                ...$data,
                'organization_id' => $data['organization_id'] ?? $creator->organization_id,
                'requested_by_user_id' => $isPortal ? $creator->id : ($data['requested_by_user_id'] ?? $creator->id),
                'status' => $status,
                'approved_by' => $status === 'approved' ? $creator->id : null,
                'approved_at' => $status === 'approved' ? now() : null,
            ]);

            $reservation->approvals()->create([
                'action' => $status === 'approved' ? 'approved' : 'requested',
                'decided_by' => $creator->id,
                'notes' => $data['notes'] ?? null,
                'old_status' => null,
                'new_status' => $status,
            ]);

            $this->activityLogger->log(
                subject: $reservation,
                action: 'space.reservation.created',
                user: $creator,
                organization: $reservation->organization,
                newValues: $reservation->only(['space_id', 'contact_id', 'status', 'start_at', 'end_at', 'purpose']),
                description: 'Reserva de espaco criada.',
            );

            if ($status === 'approved') {
                $reservation->loadMissing('space', 'organization', 'event');
                $this->reservationService->ensureReservationEvent($reservation, $creator);
                $this->reservationService->ensureCleaningRecord($reservation, $creator);
            }

            return $reservation;
        });
    }
}
