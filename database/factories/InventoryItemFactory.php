<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'organization_id' => Organization::factory(),
            'inventory_category_id' => InventoryCategory::factory(),
            'inventory_location_id' => InventoryLocation::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'sku' => strtoupper(fake()->bothify('INV-###??')),
            'item_type' => fake()->randomElement(InventoryItem::ITEM_TYPES),
            'unit' => fake()->randomElement(InventoryItem::UNITS),
            'current_stock' => fake()->randomFloat(2, 0, 120),
            'minimum_stock' => fake()->randomFloat(2, 0, 30),
            'maximum_stock' => fake()->randomFloat(2, 50, 180),
            'unit_cost' => fake()->optional()->randomFloat(2, 1, 300),
            'status' => fake()->randomElement(InventoryItem::STATUSES),
            'is_stock_tracked' => true,
            'is_loanable' => fake()->boolean(30),
            'is_active' => true,
        ];
    }
}
