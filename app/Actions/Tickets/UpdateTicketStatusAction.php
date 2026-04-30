<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatusAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(Ticket $ticket, string $newStatus, User $changedBy, ?string $notes = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $newStatus, $changedBy, $notes) {
            $oldStatus = $ticket->status;

            if ($oldStatus === $newStatus) {
                return $ticket;
            }

            $ticket->status = $newStatus;

            if (in_array($newStatus, ['fechado', 'cancelado', 'indeferido'], true)) {
                $ticket->closed_at = now();
                $ticket->closed_by = $changedBy->id;
            } elseif ($oldStatus === 'fechado') {
                $ticket->closed_at = null;
                $ticket->closed_by = null;
            }

            $ticket->save();

            $ticket->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy->id,
                'notes' => $notes,
            ]);

            $this->activityLogger->log(
                subject: $ticket,
                action: 'ticket.status_updated',
                user: $changedBy,
                organization: $ticket->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $newStatus],
                description: 'Estado do pedido alterado.',
            );

            return $ticket;
        });
    }
}
