<?php

namespace App\Services\Notifications;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Tickets\PublicTicketStatus;

class TicketNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly TicketNotificationRecipientResolver $recipientResolver,
    ) {
    }

    public function notifyTicketCreated(Ticket $ticket, ?User $actor = null): void
    {
        $recipients = $this->recipientResolver->resolveTicketCreatedRecipients($ticket, $actor);
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
        if (! PublicTicketStatus::shouldNotifyCitizenForTransition($oldStatus, $newStatus)) {
            return;
        }

        $recipient = $this->recipientResolver->resolveCitizenRecipient($ticket, $actor);

        if (! $recipient) {
            return;
        }

        $this->notificationService->createForUsers([$recipient], [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_status_changed',
            'title' => 'Atualizacao do pedido',
            'message' => sprintf(
                'O pedido %s mudou para "%s".',
                $ticket->reference,
                PublicTicketStatus::label($newStatus)
            ),
            'notifiable' => $ticket,
            'action_url' => route('portal.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_public_status' => PublicTicketStatus::label($oldStatus),
                'new_public_status' => PublicTicketStatus::label($newStatus),
            ],
        ]);
    }

    public function notifyTicketCommentAdded(Ticket $ticket, Comment $comment, ?User $actor = null): void
    {
        if (($comment->visibility ?? 'internal') === 'public') {
            $isCitizenComment = $actor?->hasAnyRole(['cidadao', 'associacao', 'empresa'])
                || ((int) $ticket->created_by === (int) $comment->user_id);

            if ($isCitizenComment) {
                $this->notifyTicketCitizenReplyAdded($ticket, $comment, $actor);

                return;
            }

            $this->notifyTicketPublicReplyAdded($ticket, $comment, $actor);

            return;
        }

        $recipients = $this->recipientResolver->resolveInternalRecipients($ticket, $actor);
        if ($recipients->isEmpty()) {
            return;
        }

        $this->notificationService->createForUsers($recipients, [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_internal_note_added',
            'title' => 'Nova nota interna',
            'message' => sprintf('Foi adicionada uma nota interna ao pedido %s.', $ticket->reference),
            'notifiable' => $ticket,
            'action_url' => route('admin.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
                'comment_id' => $comment->id,
                'visibility' => $comment->visibility,
            ],
        ]);
    }

    public function notifyTicketPublicReplyAdded(Ticket $ticket, Comment $comment, ?User $actor = null): void
    {
        $recipient = $this->recipientResolver->resolveCitizenRecipient($ticket, $actor);
        if (! $recipient) {
            return;
        }

        $this->notificationService->createForUsers([$recipient], [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_public_reply_added',
            'title' => 'Nova resposta da Junta',
            'message' => sprintf('Recebeu uma nova resposta no pedido %s.', $ticket->reference),
            'notifiable' => $ticket,
            'action_url' => route('portal.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
                'comment_id' => $comment->id,
            ],
        ]);
    }

    public function notifyTicketCitizenReplyAdded(Ticket $ticket, Comment $comment, ?User $actor = null): void
    {
        $recipients = $this->recipientResolver->resolveInternalRecipients($ticket, $actor);
        if ($recipients->isEmpty()) {
            return;
        }

        $this->notificationService->createForUsers($recipients, [
            'organization_id' => $ticket->organization_id,
            'type' => 'ticket_citizen_reply_added',
            'title' => 'Nova mensagem do municipe',
            'message' => sprintf('O municipe adicionou informacao ao pedido %s.', $ticket->reference),
            'notifiable' => $ticket,
            'action_url' => route('admin.tickets.show', $ticket, false),
            'priority' => $ticket->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'ticket_id' => $ticket->id,
                'ticket_reference' => $ticket->reference,
                'comment_id' => $comment->id,
            ],
        ]);
    }
}
