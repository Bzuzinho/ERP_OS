<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;

class InventoryRestockService
{
    public function suggestQuantity(InventoryItem $item): float
    {
        $current = (float) $item->current_stock;

        if ($item->maximum_stock !== null) {
            $suggested = (float) $item->maximum_stock - $current;

            return max(1, round($suggested, 2));
        }

        $minimum = (float) ($item->minimum_stock ?? 1);
        $suggested = $minimum - $current;

        return max(1, round($suggested, 2));
    }
}
