<?php

namespace App\Services\Spaces;

use App\Models\Event;
use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class SpaceReservationService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly SpaceCleaningService $spaceCleaningService,
    ) {
    }

    public function ensureReservationEvent(SpaceReservation $reservation, User $performedBy): Event
    {
        if ($reservation->event) {
            return $reservation->event;
        }

        $event = Event::create([
            'organization_id' => $reservation->organization_id,
            'title' => 'Reserva de Espaco: '.$reservation->space->name,
            'description' => $reservation->purpose,
            'event_type' => 'reservation',
            'status' => 'confirmed',
            'start_at' => $reservation->start_at,
            'end_at' => $reservation->end_at,
            'location_text' => $reservation->space->location_text,
            'created_by' => $performedBy->id,
            'related_contact_id' => $reservation->contact_id,
            'visibility' => 'internal',
        ]);

        $reservation->event()->associate($event);
        $reservation->save();

        $this->activityLogger->log(
            subject: $event,
            action: 'event.created_from_space_reservation',
            user: $performedBy,
            organization: $event->organization,
            newValues: $event->only(['title', 'event_type', 'status', 'start_at', 'end_at']),
            description: 'Evento criado automaticamente a partir da aprovacao de reserva.',
        );

        return $event;
    }

    public function cancelReservationEvent(SpaceReservation $reservation, User $performedBy): void
    {
        if (! $reservation->event) {
            return;
        }

        $reservation->event->status = 'cancelled';
        $reservation->event->save();

        $this->activityLogger->log(
            subject: $reservation->event,
            action: 'event.cancelled_from_space_reservation',
            user: $performedBy,
            organization: $reservation->event->organization,
            oldValues: ['status' => 'confirmed'],
            newValues: ['status' => 'cancelled'],
            description: 'Evento associado cancelado apos cancelamento de reserva.',
        );
    }

    public function ensureCleaningRecord(SpaceReservation $reservation, User $performedBy): void
    {
        if (! $reservation->space->has_cleaning_required) {
            return;
        }

        $cleaningRecord = $this->spaceCleaningService->ensureReservationCleaning($reservation, $performedBy);

        $this->activityLogger->log(
            subject: $cleaningRecord,
            action: 'space.cleaning.auto_created',
            user: $performedBy,
            organization: $reservation->organization,
            newValues: $cleaningRecord->only(['space_id', 'space_reservation_id', 'status', 'scheduled_at']),
            description: 'Registo de limpeza criado automaticamente para reserva aprovada.',
        );
    }
}
