<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryRestockRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryRestockRequest>
 */
class InventoryRestockRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'requested_by' => User::factory(),
            'approved_by' => null,
            'quantity_requested' => fake()->randomFloat(2, 1, 50),
            'quantity_approved' => null,
            'status' => 'requested',
            'reason' => fake()->optional()->sentence(),
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'completed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
