<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\TicketNotificationService;
use App\Services\Tickets\TicketResolutionService;
use App\Services\Tickets\TicketStateTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class SubmitTicketForValidationAction
{
    public function __construct(
        private readonly TicketResolutionService $ticketResolutionService,
        private readonly TicketStateTransitionService $stateTransitionService,
        private readonly TicketNotificationService $ticketNotificationService,
    ) {
    }

    public function execute(Ticket $ticket, User $actor, ?string $resolutionNotes = null, ?string $notes = null): Ticket
    {
        if (! $actor->can('submitForValidation', $ticket)) {
            throw new AuthorizationException('Sem permissão para enviar o pedido para validação.');
        }

        if (! $this->ticketResolutionService->canSubmitForValidation($ticket)) {
            throw ValidationException::withMessages([
                'ticket' => 'O pedido ainda não está pronto para validação.',
            ]);
        }

        $oldStatus = $ticket->status;

        $ticket = $this->stateTransitionService->transition(
            ticket: $ticket,
            newStatus: 'aguarda_validacao',
            changedBy: $actor,
            notes: $notes ?? 'Pedido submetido para validação operacional.',
            attributes: [
                'resolution_notes' => $resolutionNotes ?? $ticket->resolution_notes,
            ],
            allowValidationSubmission: true,
        )->fresh();

        $this->ticketNotificationService->notifyTicketSubmittedForValidation($ticket, $actor);

        if ($oldStatus !== 'aguarda_validacao') {
            $this->ticketNotificationService->notifyTicketStatusChanged($ticket, $oldStatus, 'aguarda_validacao', $actor);
        }

        return $ticket;
    }
}