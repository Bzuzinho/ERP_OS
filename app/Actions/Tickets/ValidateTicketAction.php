<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\TicketNotificationService;
use App\Services\Tickets\TicketStateTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ValidateTicketAction
{
    public function __construct(
        private readonly TicketStateTransitionService $stateTransitionService,
        private readonly TicketNotificationService $ticketNotificationService,
    ) {
    }

    public function execute(Ticket $ticket, User $validator, ?string $notes = null, ?string $resolutionNotes = null): Ticket
    {
        if (! $validator->can('validate', $ticket)) {
            throw new AuthorizationException('Sem permissão para validar o pedido.');
        }

        if (! $ticket->canBeValidated()) {
            throw ValidationException::withMessages([
                'ticket' => 'O pedido não pode ser validado no estado atual.',
            ]);
        }

        $oldStatus = $ticket->status;

        $ticket = $this->stateTransitionService->transition(
            ticket: $ticket,
            newStatus: 'resolvido',
            changedBy: $validator,
            notes: $notes,
            attributes: [
                'validated_at' => now(),
                'validated_by' => $validator->id,
                'validation_notes' => $notes,
                'resolution_notes' => $resolutionNotes,
            ],
        )->fresh();

        $this->ticketNotificationService->notifyTicketValidated($ticket, $validator);
        $this->ticketNotificationService->notifyTicketStatusChanged($ticket, $oldStatus, 'resolvido', $validator);

        return $ticket;
    }
}
