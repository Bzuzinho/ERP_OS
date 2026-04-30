<?php

namespace App\Services\Inventory;

use App\Models\InventoryMovement;
use Illuminate\Support\Carbon;

class InventoryMovementService
{
    public function create(array $data): InventoryMovement
    {
        $quantity = (float) ($data['quantity'] ?? 0);
        $unitCost = isset($data['unit_cost']) ? (float) $data['unit_cost'] : null;

        return InventoryMovement::create([
            ...$data,
            'occurred_at' => $data['occurred_at'] ?? Carbon::now(),
            'total_cost' => $unitCost !== null ? round($quantity * $unitCost, 2) : null,
        ]);
    }
}
