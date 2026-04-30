<?php

namespace App\Actions\Inventory;

use App\Models\InventoryLoan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnInventoryLoanAction
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(InventoryLoan $loan, User $performedBy, ?string $returnNotes = null): InventoryLoan
    {
        return DB::transaction(function () use ($loan, $performedBy, $returnNotes) {
            if (! in_array($loan->status, ['active', 'overdue'], true)) {
                throw new RuntimeException('Apenas emprestimos ativos ou em atraso podem ser devolvidos.');
            }

            $loan->status = 'returned';
            $loan->returned_at = now();
            $loan->returned_to = $performedBy->id;
            $loan->return_notes = $returnNotes;
            $loan->save();

            $item = $loan->item()->firstOrFail();

            $this->registerMovementAction->execute($item, $performedBy, [
                'movement_type' => 'return',
                'quantity' => (float) $loan->quantity,
                'related_ticket_id' => $loan->related_ticket_id,
                'related_task_id' => $loan->related_task_id,
                'related_event_id' => $loan->related_event_id,
                'related_space_reservation_id' => $loan->related_space_reservation_id,
                'notes' => $returnNotes ?? 'Devolucao de emprestimo.',
            ]);

            $this->activityLogger->log(
                subject: $loan,
                action: 'inventory.loan.returned',
                user: $performedBy,
                organization: $loan->organization,
                oldValues: ['status' => 'active'],
                newValues: ['status' => 'returned', 'returned_at' => $loan->returned_at],
                description: 'Emprestimo devolvido.',
            );

            return $loan;
        });
    }
}
