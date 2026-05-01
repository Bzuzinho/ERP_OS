<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\OperationalPlan;
use App\Models\OperationalPlanResource;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalPlanResource>
 */
class OperationalPlanResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operational_plan_id' => OperationalPlan::factory(),
            'inventory_item_id' => fake()->boolean(50) ? InventoryItem::factory() : null,
            'space_id' => fake()->boolean(50) ? Space::factory() : null,
            'quantity' => fake()->optional()->randomFloat(2, 1, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
