<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 15);
        $unitCost = fake()->optional()->randomFloat(2, 1, 50);

        return [
            'organization_id' => Organization::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'movement_type' => fake()->randomElement(InventoryMovement::TYPES),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost ? round($quantity * $unitCost, 2) : null,
            'from_location_id' => null,
            'to_location_id' => null,
            'requested_by' => User::factory(),
            'handled_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
            'occurred_at' => now(),
        ];
    }
}
