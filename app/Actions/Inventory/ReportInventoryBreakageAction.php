<?php

namespace App\Actions\Inventory;

use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use App\Models\User;
use App\Services\Inventory\InventoryStockService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportInventoryBreakageAction
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
        private readonly InventoryStockService $stockService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $performedBy, array $data): InventoryBreakage
    {
        return DB::transaction(function () use ($performedBy, $data) {
            /** @var InventoryItem $item */
            $item = InventoryItem::query()->findOrFail($data['inventory_item_id']);
            $quantity = (float) $data['quantity'];

            if ($item->is_stock_tracked && ! $this->stockService->hasSufficientStock($item, $quantity)) {
                throw new RuntimeException('Stock insuficiente para reportar quebra.');
            }

            $breakage = InventoryBreakage::create([
                ...$data,
                'organization_id' => $performedBy->organization_id,
                'reported_by' => $performedBy->id,
                'status' => $data['status'] ?? 'reported',
            ]);

            $movement = $this->registerMovementAction->execute($item, $performedBy, [
                'movement_type' => 'breakage',
                'quantity' => $quantity,
                'related_ticket_id' => $data['related_ticket_id'] ?? null,
                'related_task_id' => $data['related_task_id'] ?? null,
                'notes' => $data['description'] ?? 'Quebra de inventario reportada.',
            ]);

            $breakage->inventory_movement_id = $movement->id;
            $breakage->save();

            $this->activityLogger->log(
                subject: $breakage,
                action: 'inventory.breakage.reported',
                user: $performedBy,
                organization: $breakage->organization,
                newValues: $breakage->only(['inventory_item_id', 'quantity', 'breakage_type', 'status']),
                description: 'Quebra de inventario reportada.',
            );

            return $breakage;
        });
    }
}
