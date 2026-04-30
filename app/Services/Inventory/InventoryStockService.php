<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use RuntimeException;

class InventoryStockService
{
    public function hasSufficientStock(InventoryItem $item, float $quantity): bool
    {
        if (! $item->is_stock_tracked) {
            return true;
        }

        return (float) $item->current_stock >= $quantity;
    }

    public function applyMovement(
        InventoryItem $item,
        string $movementType,
        float $quantity,
        ?float $signedQuantity = null,
        bool $canAdjustNegative = false,
    ): float {
        $currentStock = (float) $item->current_stock;
        $delta = 0.0;

        if (in_array($movementType, ['entry', 'restock', 'return'], true)) {
            $delta = $quantity;
        } elseif (in_array($movementType, ['exit', 'consumption', 'loan', 'breakage'], true)) {
            $delta = -$quantity;
        } elseif (in_array($movementType, ['adjustment', 'correction'], true)) {
            $delta = $signedQuantity ?? $quantity;
        }

        $newStock = $currentStock + $delta;

        if ($item->is_stock_tracked && $newStock < 0 && ! ($canAdjustNegative && in_array($movementType, ['adjustment', 'correction'], true))) {
            throw new RuntimeException('Stock insuficiente para esta operacao.');
        }

        return round($newStock, 2);
    }

    public function isBelowMinimum(InventoryItem $item): bool
    {
        if ($item->minimum_stock === null || ! $item->is_stock_tracked) {
            return false;
        }

        return (float) $item->current_stock < (float) $item->minimum_stock;
    }

    public function getStockStatus(InventoryItem $item): string
    {
        if (! $item->is_stock_tracked) {
            return 'ok';
        }

        if ((float) $item->current_stock <= 0) {
            return 'out';
        }

        return $this->isBelowMinimum($item) ? 'low' : 'ok';
    }
}
