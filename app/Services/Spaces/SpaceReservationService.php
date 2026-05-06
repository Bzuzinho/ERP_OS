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
    ) {
    }

    public function ensureReservationEvent(SpaceReservation $reservation, User $performedBy): Event
    {
        if ($reservation->event) {
            return $reservation->event;
        }

        $event = Event::create([
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

    /**
     * Create internal preparation and cleaning tasks for the reservation.
     * Tasks are assigned to the space's manager or to a configurable role.
     */
    public function createReservationTasks(SpaceReservation $reservation, User $performedBy): void
    {
        // Load necessary relationships
        $reservation->loadMissing('space', 'organization');

        // Determine assignee (space manager or fallback to system user)
        $assigneeId = null;
        if ($reservation->space->managed_by) {
            $assigneeId = $reservation->space->managed_by;
        }

        // Create preparation task (due before start_at)
        $preparationTask = Task::create([
            'organization_id' => $reservation->organization_id,
            'space_reservation_id' => $reservation->id,
            'assigned_to' => $assigneeId,
            'created_by' => $performedBy->id,
            'title' => 'Preparar espaco: '.$reservation->space->name,
            'description' => 'Preparar o espaço para a reserva: '.$reservation->purpose,
            'status' => 'pending',
            'priority' => 'normal',
            'due_date' => $reservation->start_at->copy()->subHours(1)->toDateString(),
        ]);

        $this->activityLogger->log(
            subject: $preparationTask,
            action: 'task.created_from_space_reservation',
            user: $performedBy,
            organization: $reservation->organization,
            newValues: $preparationTask->only(['title', 'space_reservation_id', 'due_date']),
            description: 'Tarefa de preparacao criada automaticamente para reserva aprovada.',
        );

        // Create cleaning task (due after end_at)
        $cleaningTask = Task::create([
            'organization_id' => $reservation->organization_id,
            'space_reservation_id' => $reservation->id,
            'assigned_to' => $assigneeId,
            'created_by' => $performedBy->id,
            'title' => 'Limpeza apos reserva: '.$reservation->space->name,
            'description' => 'Proceder a limpeza do espaço apos a reserva: '.$reservation->purpose,
            'status' => 'pending',
            'priority' => 'normal',
            'due_date' => $reservation->end_at->copy()->addHours(2)->toDateString(),
        ]);

        $this->activityLogger->log(
            subject: $cleaningTask,
            action: 'task.created_from_space_reservation',
            user: $performedBy,
            organization: $reservation->organization,
            newValues: $cleaningTask->only(['title', 'space_reservation_id', 'due_date']),
            description: 'Tarefa de limpeza criada automaticamente para reserva aprovada.',
        );
    }

    /**
     * Cancel any tasks linked to this reservation.
     */
    public function cancelReservationTasks(SpaceReservation $reservation, User $performedBy): void
    {
        $tasks = $reservation->tasks()->where('status', '!=', 'done')->where('status', '!=', 'cancelled')->get();

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
