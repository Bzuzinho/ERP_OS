<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketStateTransitionService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function transition(
        Ticket $ticket,
        string $newStatus,
        User $changedBy,
        ?string $notes = null,
        array $attributes = [],
        bool $allowValidationSubmission = false,
    ): Ticket {
        return DB::transaction(function () use ($ticket, $newStatus, $changedBy, $notes, $attributes, $allowValidationSubmission) {
            $oldStatus = $ticket->status;

            if ($newStatus === 'aguarda_validacao' && ! $allowValidationSubmission) {
                throw ValidationException::withMessages([
                    'status' => 'A transição para aguarda_validacao deve ser feita pelo fluxo de envio para validação.',
                ]);
            }

            $ticket->fill($attributes);

            if ($oldStatus !== $newStatus) {
                $ticket->status = $newStatus;

                if (in_array($newStatus, ['fechado', 'cancelado', 'indeferido'], true)) {
                    $ticket->closed_at = now();
                    $ticket->closed_by = $changedBy->id;
                } elseif (in_array($oldStatus, ['fechado', 'cancelado', 'indeferido'], true)) {
                    $ticket->closed_at = null;
                    $ticket->closed_by = null;
                }
            }

            if (! $ticket->isDirty()) {
                return $ticket;
            }

            $ticket->save();

            if ($oldStatus !== $newStatus) {
                $ticket->statusHistories()->create([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $changedBy->id,
                    'notes' => $notes,
                ]);
            }

            $this->activityLogger->log(
                subject: $ticket,
                action: 'ticket.status_updated',
                user: $changedBy,
                organization: $ticket->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $ticket->status],
                description: 'Estado do pedido alterado.',
            );

            return $ticket;
        });
    }
}
