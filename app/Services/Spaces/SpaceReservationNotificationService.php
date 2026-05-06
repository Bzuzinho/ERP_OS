<?php

namespace App\Services\Spaces;

use App\Models\SpaceReservation;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class SpaceReservationNotificationService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    /**
     * Send notification when a new reservation is requested.
     */
    public function notifyReservationRequested(SpaceReservation $reservation, User $requestedBy): void
    {
        // Notify space managers and admin
        $spaceManager = $reservation->space->manager();
        $recipients = [];

        if ($spaceManager) {
            $recipients[] = $spaceManager;
        }

        // Also notify users with spaces.approve_reservation permission
        $recipients = array_merge(
            $recipients,
            User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'administrativo']))
                ->where('organization_id', $reservation->organization_id)
                ->pluck('id')
                ->toArray()
        );

        if (empty($recipients)) {
            return;
        }

        $this->notificationService->createForUsers(
            array_unique($recipients),
            [
                'organization_id' => $reservation->organization_id,
                'type' => 'space_reservation',
                'title' => 'Nova reserva de espaço pendente',
                'message' => "Nova reserva solicitada para {$reservation->space->name} em {$reservation->start_at->format('d/m/Y H:i')}",
                'notifiable' => $reservation,
                'action_url' => route('admin.space-reservations.show', $reservation),
                'priority' => 'high',
                'created_by' => $requestedBy->id,
            ]
        );
    }

    /**
     * Send notification when a reservation is approved.
     */
    public function notifyReservationApproved(SpaceReservation $reservation, User $approvedBy): void
    {
        // Notify requester
        $recipients = [];

        if ($reservation->requested_by_user_id) {
            $recipients[] = $reservation->requested_by_user_id;
        }

        if ($reservation->contact && $reservation->contact->user_id) {
            $recipients[] = $reservation->contact->user_id;
        }

        if (empty($recipients)) {
            return;
        }

        $this->notificationService->createForUsers(
            array_unique($recipients),
            [
                'organization_id' => $reservation->organization_id,
                'type' => 'space_reservation_approved',
                'title' => 'Reserva aprovada',
                'message' => "Sua reserva para {$reservation->space->name} em {$reservation->start_at->format('d/m/Y H:i')} foi aprovada.",
                'notifiable' => $reservation,
                'action_url' => route('portal.space-reservations.show', $reservation),
                'priority' => 'high',
                'created_by' => $approvedBy->id,
            ]
        );
    }

    /**
     * Send notification when a reservation is rejected.
     */
    public function notifyReservationRejected(SpaceReservation $reservation, User $rejectedBy): void
    {
        // Notify requester
        $recipients = [];

        if ($reservation->requested_by_user_id) {
            $recipients[] = $reservation->requested_by_user_id;
        }

        if ($reservation->contact && $reservation->contact->user_id) {
            $recipients[] = $reservation->contact->user_id;
        }

        if (empty($recipients)) {
            return;
        }

        $this->notificationService->createForUsers(
            array_unique($recipients),
            [
                'organization_id' => $reservation->organization_id,
                'type' => 'space_reservation_rejected',
                'title' => 'Reserva rejeitada',
                'message' => "Sua reserva para {$reservation->space->name} foi rejeitada. Motivo: {$reservation->rejection_reason}",
                'notifiable' => $reservation,
                'action_url' => route('portal.space-reservations.show', $reservation),
                'priority' => 'high',
                'created_by' => $rejectedBy->id,
            ]
        );
    }

    /**
     * Send notification when a reservation is cancelled.
     */
    public function notifyReservationCancelled(SpaceReservation $reservation, User $cancelledBy): void
    {
        // Notify requester and relevant staff
        $recipients = [];

        if ($reservation->requested_by_user_id) {
            $recipients[] = $reservation->requested_by_user_id;
        }

        if ($reservation->contact && $reservation->contact->user_id) {
            $recipients[] = $reservation->contact->user_id;
        }

        // Notify space manager
        if ($reservation->space->manager()) {
            $recipients[] = $reservation->space->manager()->id;
        }

        if (empty($recipients)) {
            return;
        }

        $this->notificationService->createForUsers(
            array_unique($recipients),
            [
                'organization_id' => $reservation->organization_id,
                'type' => 'space_reservation_cancelled',
                'title' => 'Reserva cancelada',
                'message' => "A reserva para {$reservation->space->name} em {$reservation->start_at->format('d/m/Y H:i')} foi cancelada.",
                'notifiable' => $reservation,
                'action_url' => route('portal.space-reservations.show', $reservation),
                'priority' => 'high',
                'created_by' => $cancelledBy->id,
            ]
        );
    }

    /**
     * Send notification to task assignee when a task is created.
     */
    public function notifyTaskCreated($task, User $createdBy): void
    {
        if (! $task->assigned_to) {
            return;
        }

        $this->notificationService->createForUsers(
            [$task->assigned_to],
            [
                'organization_id' => $task->organization_id,
                'type' => 'task_created',
                'title' => 'Nova tarefa atribuida',
                'message' => "Tarefa: {$task->title}",
                'notifiable' => $task,
                'action_url' => route('admin.tasks.show', $task),
                'priority' => 'normal',
                'created_by' => $createdBy->id,
            ]
        );
    }
}
