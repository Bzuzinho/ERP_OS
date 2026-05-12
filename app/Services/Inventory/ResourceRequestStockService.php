<?php

namespace App\Services\Inventory;

use App\Actions\Inventory\RegisterInventoryMovementAction;
use App\Models\InventoryLoan;
use App\Models\ResourceRequest;
use App\Models\ResourceRequestItem;
use App\Models\User;

class ResourceRequestStockService
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
    ) {
    }

    public function deliver(ResourceRequest $request, ResourceRequestItem $item, User $performedBy, float $quantity): void
    {
        $inventoryItem = $item->inventoryItem;

        if ($inventoryItem->is_loanable) {
            InventoryLoan::create([
                'organization_id' => $request->organization_id,
                'inventory_item_id' => $inventoryItem->id,
                'resource_request_id' => $request->id,
                'resource_request_item_id' => $item->id,
                'borrower_user_id' => $request->requested_by,
                'quantity' => $quantity,
                'loaned_at' => now(),
                'expected_return_at' => $request->needed_until,
                'status' => 'active',
                'loaned_by' => $performedBy->id,
                'notes' => 'Emprestimo criado a partir da requisicao de recurso.',
            ]);
        }

        if ($inventoryItem->is_stock_tracked && $inventoryItem->item_type === 'consumable') {
            $this->registerMovementAction->execute($inventoryItem, $performedBy, [
                'movement_type' => 'consumption',
                'quantity' => $quantity,
                'resource_request_id' => $request->id,
                'resource_request_item_id' => $item->id,
                'notes' => 'Consumo por entrega de requisicao de recurso.',
            ]);
        }
    }

    public function return(ResourceRequest $request, ResourceRequestItem $item, User $performedBy, float $quantity): void
    {
        $inventoryItem = $item->inventoryItem;

        if ($inventoryItem->is_loanable) {
            $loan = InventoryLoan::query()
                ->where('resource_request_id', $request->id)
                ->where('resource_request_item_id', $item->id)
                ->latest('id')
                ->first();

            if ($loan !== null && (float) $item->quantity_returned >= (float) $item->quantity_delivered) {
                $loan->update([
                    'status' => 'returned',
                    'returned_at' => now(),
                    'returned_to' => $performedBy->id,
                ]);
            }
        }

        if ($inventoryItem->is_stock_tracked && $inventoryItem->item_type === 'consumable' && $quantity > 0) {
            $this->registerMovementAction->execute($inventoryItem, $performedBy, [
                'movement_type' => 'return',
                'quantity' => $quantity,
                'resource_request_id' => $request->id,
                'resource_request_item_id' => $item->id,
                'notes' => 'Reposicao de stock por devolucao de requisicao de recurso.',
            ]);
        }
    }
}
