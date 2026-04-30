<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class AssignTicketAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(Ticket $ticket, ?int $assignedTo, User $performedBy): Ticket
    {
        $oldAssignee = $ticket->assigned_to;

        $ticket->assigned_to = $assignedTo;
        $ticket->save();

        $this->activityLogger->log(
            subject: $ticket,
            action: 'ticket.assigned',
            user: $performedBy,
            organization: $ticket->organization,
            oldValues: ['assigned_to' => $oldAssignee],
            newValues: ['assigned_to' => $assignedTo],
            description: 'Responsavel do pedido alterado.',
        );

        return $ticket;
    }
}
