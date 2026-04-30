<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Inventory\InventoryMovementService;
use App\Services\Inventory\InventoryStockService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegisterInventoryMovementAction
{
    public function __construct(
        private readonly InventoryMovementService $movementService,
        private readonly InventoryStockService $stockService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(InventoryItem $item, User $performedBy, array $data): InventoryMovement
    {
        return DB::transaction(function () use ($item, $performedBy, $data) {
            $type = (string) ($data['movement_type'] ?? 'entry');
            $quantity = (float) ($data['quantity'] ?? 0);

            if (! in_array($type, InventoryMovement::TYPES, true)) {
                throw new RuntimeException('Tipo de movimento invalido.');
            }

            if ($quantity <= 0) {
                throw new RuntimeException('Quantidade deve ser superior a zero.');
            }

            if (in_array($type, ['exit', 'consumption', 'loan', 'breakage'], true) && ! $this->stockService->hasSufficientStock($item, $quantity)) {
                throw new RuntimeException('Stock insuficiente para registar o movimento.');
            }

            $allowNegative = $performedBy->can('inventory.adjust') && in_array($type, ['adjustment', 'correction'], true);
            $signedQuantity = isset($data['signed_quantity']) ? (float) $data['signed_quantity'] : null;

            $newStock = $this->stockService->applyMovement(
                item: $item,
                movementType: $type,
                quantity: $quantity,
                signedQuantity: $signedQuantity,
                canAdjustNegative: $allowNegative,
            );

            $movement = $this->movementService->create([
                ...$data,
                'organization_id' => $item->organization_id,
                'inventory_item_id' => $item->id,
                'requested_by' => $data['requested_by'] ?? $performedBy->id,
                'handled_by' => $data['handled_by'] ?? $performedBy->id,
                'quantity' => $quantity,
                'movement_type' => $type,
            ]);

            $item->current_stock = $newStock;

            if (
                $type === 'transfer'
                && ! empty($data['from_location_id'])
                && ! empty($data['to_location_id'])
                && (int) $item->inventory_location_id === (int) $data['from_location_id']
                && round($quantity, 2) >= round((float) $item->getOriginal('current_stock'), 2)
            ) {
                $item->inventory_location_id = (int) $data['to_location_id'];
            }

            $item->save();

            $this->activityLogger->log(
                subject: $movement,
                action: 'inventory.movement.created',
                user: $performedBy,
                organization: $item->organization,
                newValues: $movement->only(['movement_type', 'quantity', 'inventory_item_id', 'occurred_at']),
                description: 'Movimento de inventario registado.',
            );

            if ($item->minimum_stock !== null && $this->stockService->isBelowMinimum($item)) {
                InventoryRestockRequest::query()->firstOrCreate(
                    [
                        'organization_id' => $item->organization_id,
                        'inventory_item_id' => $item->id,
                        'status' => 'requested',
                    ],
                    [
                        'requested_by' => $performedBy->id,
                        'quantity_requested' => max(1, (float) $item->minimum_stock - (float) $item->current_stock),
                        'reason' => 'Criado automaticamente por stock abaixo do minimo.',
                        'notes' => 'Pedido automatico de reposicao.',
                    ],
                );
            }

            return $movement;
        });
    }
}
