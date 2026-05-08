<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\TicketStateTransitionService;
use Illuminate\Validation\ValidationException;

class CancelTicketAction
{
    public function __construct(
        private readonly TicketStateTransitionService $stateTransitionService,
    ) {
    }

    public function execute(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        if (! $ticket->canBeCancelled()) {
            throw ValidationException::withMessages([
                'ticket' => 'O pedido não pode ser cancelado no estado atual.',
            ]);
        }

        return $this->stateTransitionService->transition(
            ticket: $ticket,
            newStatus: 'cancelado',
            changedBy: $actor,
            notes: $notes,
            attributes: [
                'resolution_notes' => $notes,
            ],
        );
    }
}
