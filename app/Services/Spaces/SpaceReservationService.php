<?php

namespace App\Services\Spaces;

use App\Models\Event;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class SpaceReservationService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly SpaceCleaningService $spaceCleaningService,
        private readonly SpaceReservationNotificationService $notificationService,
    ) {
    }

    public function ensureReservationEvent(SpaceReservation $reservation, User $performedBy): Event
    {
        $reservation->loadMissing('event', 'space', 'organization');

        $eventPayload = [
            'organization_id' => $reservation->organization_id,
            'space_id' => $reservation->space_id,
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
        ];

        if ($reservation->event) {
            $event = $reservation->event;
            $oldValues = $event->only(['space_id', 'event_type', 'status', 'start_at', 'end_at']);

            $event->fill($eventPayload);

            if ($event->isDirty()) {
                $event->save();

                $this->activityLogger->log(
                    subject: $event,
                    action: 'event.synced_from_space_reservation',
                    user: $performedBy,
                    organization: $event->organization,
                    oldValues: $oldValues,
                    newValues: $event->only(['space_id', 'event_type', 'status', 'start_at', 'end_at']),
                    description: 'Evento associado sincronizado com a reserva aprovada.',
                );
            }

            return $event;
        }

        $event = Event::create($eventPayload);

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

    /**
     * Create internal preparation and cleaning tasks for the reservation.
     * Tasks are assigned to the space's manager or to a configurable role.
     */
    public function createReservationTasks(SpaceReservation $reservation, User $performedBy): void
    {
        $reservation->loadMissing('space', 'organization');

        $assigneeId = $reservation->space->getAttribute('managed_by');
        $assigneeId = is_numeric($assigneeId) ? (int) $assigneeId : null;

        $taskTemplates = [
            [
                'title' => 'Preparar espaco: '.$reservation->space->name,
                'description' => 'Preparar o espaço para a reserva: '.$reservation->purpose,
                'due_date' => $reservation->start_at->copy()->subHours(1)->toDateString(),
            ],
            [
                'title' => 'Limpeza apos reserva: '.$reservation->space->name,
                'description' => 'Proceder a limpeza do espaço apos a reserva: '.$reservation->purpose,
                'due_date' => $reservation->end_at->copy()->addHours(2)->toDateString(),
            ],
        ];

        foreach ($taskTemplates as $taskTemplate) {
            $task = Task::firstOrCreate(
                [
                    'space_reservation_id' => $reservation->id,
                    'title' => $taskTemplate['title'],
                ],
                [
                    'organization_id' => $reservation->organization_id,
                    'assigned_to' => $assigneeId,
                    'created_by' => $performedBy->id,
                    'description' => $taskTemplate['description'],
                    'status' => 'pending',
                    'priority' => 'normal',
                    'due_date' => $taskTemplate['due_date'],
                ]
            );

            if (! $task->wasRecentlyCreated) {
                continue;
            }

            $this->activityLogger->log(
                subject: $task,
                action: 'task.created_from_space_reservation',
                user: $performedBy,
                organization: $reservation->organization,
                newValues: $task->only(['title', 'space_reservation_id', 'status', 'due_date', 'assigned_to']),
                description: 'Tarefa operacional criada automaticamente para reserva aprovada.',
            );

            $this->notificationService->notifyTaskCreated($task, $performedBy);
        }
    }

    /**
     * Cancel any tasks linked to this reservation.
     */
    public function cancelReservationTasks(SpaceReservation $reservation, User $performedBy): void
    {
        $tasks = $reservation->tasks()->whereNotIn('status', ['done', 'cancelled', 'validated'])->get();

        foreach ($tasks as $task) {
            $task->status = 'cancelled';
            $task->save();

            $this->activityLogger->log(
                subject: $task,
                action: 'task.cancelled_from_space_reservation',
                user: $performedBy,
                organization: $reservation->organization,
                oldValues: ['status' => $task->getOriginal('status')],
                newValues: ['status' => 'cancelled'],
                description: 'Tarefa cancelada por cancelamento de reserva.',
            );
        }
    }
}
