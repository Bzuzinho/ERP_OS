<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\User;
use App\Services\Inventory\InventoryStockService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateInventoryLoanAction
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
        private readonly InventoryStockService $stockService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $performedBy, array $data): InventoryLoan
    {
        return DB::transaction(function () use ($performedBy, $data) {
            /** @var InventoryItem $item */
            $item = InventoryItem::query()->findOrFail($data['inventory_item_id']);
            $quantity = (float) $data['quantity'];

            if (! $item->is_loanable) {
                throw new RuntimeException('Este item nao esta disponivel para emprestimo.');
            }

            if ($item->is_stock_tracked && ! $this->stockService->hasSufficientStock($item, $quantity)) {
                throw new RuntimeException('Stock insuficiente para emprestimo.');
            }

            $loan = InventoryLoan::create([
                ...$data,
                'organization_id' => $performedBy->organization_id,
                'inventory_item_id' => $item->id,
                'loaned_at' => $data['loaned_at'] ?? now(),
                'status' => 'active',
                'loaned_by' => $performedBy->id,
            ]);

            $this->registerMovementAction->execute($item, $performedBy, [
                'movement_type' => 'loan',
                'quantity' => $quantity,
                'related_ticket_id' => $data['related_ticket_id'] ?? null,
                'related_task_id' => $data['related_task_id'] ?? null,
                'related_event_id' => $data['related_event_id'] ?? null,
                'related_space_reservation_id' => $data['related_space_reservation_id'] ?? null,
                'notes' => $data['notes'] ?? 'Emprestimo de inventario.',
            ]);

            $this->activityLogger->log(
                subject: $loan,
                action: 'inventory.loan.created',
                user: $performedBy,
                organization: $loan->organization,
                newValues: $loan->only(['inventory_item_id', 'quantity', 'status', 'expected_return_at']),
                description: 'Emprestimo de inventario criado.',
            );

            return $loan;
        });
    }
}
