<?php

namespace App\Services\Notifications;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class TicketNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly NotificationRecipientResolver $recipientResolver,
    ) {
    }

    public function notifyTicketCreated(Ticket $ticket, ?User $actor = null): void
    {
        $recipients = $this->recipientResolver->resolveForTicket($ticket, $actor);
        if ($recipients->isEmpty()) {
            return;
        }

        $this->notificationService->createForUsers($recipients, [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_created',
            'title' => 'Novo pedido',
            'message' => 'Foi criado um novo pedido atribuido a sua area de responsabilidade.',
            'notifiable' => $ticket,
            'action_url' => route('admin.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
                'ticket_status' => $ticket->status,
            ],
        ]);
    }

    public function notifyTicketAssigned(Ticket $ticket, ?User $actor = null): void
    {
        if (! $ticket->assigned_to) {
            return;
        }

        $assignee = User::query()->find($ticket->assigned_to);
        if (! $assignee || ! $assignee->is_active) {
            return;
        }

        $this->notificationService->createForUsers([$assignee], [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_assigned',
            'title' => 'Pedido atribuido',
            'message' => 'Foi-lhe atribuido um pedido para acompanhamento.',
            'notifiable' => $ticket,
            'action_url' => route('admin.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
            ],
        ]);
    }

    public function notifyTicketStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus, ?User $actor = null): void
    {
        // Reserved for next sprint.
    }

    public function notifyTicketCommentAdded(Ticket $ticket, Comment $comment, ?User $actor = null): void
    {
        // Reserved for next sprint.
    }
}
