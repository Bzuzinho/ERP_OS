<?php

namespace Database\Factories;

use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryBreakage>
 */
class InventoryBreakageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'inventory_movement_id' => null,
            'reported_by' => User::factory(),
            'related_ticket_id' => null,
            'related_task_id' => null,
            'quantity' => fake()->randomFloat(2, 1, 5),
            'breakage_type' => fake()->randomElement(InventoryBreakage::TYPES),
            'status' => 'reported',
            'description' => fake()->optional()->sentence(),
            'resolution_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }
}
