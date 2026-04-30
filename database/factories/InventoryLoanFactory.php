<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLoan>
 */
class InventoryLoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'borrower_user_id' => User::factory(),
            'borrower_contact_id' => null,
            'quantity' => fake()->randomFloat(2, 1, 5),
            'loaned_at' => now()->subDays(2),
            'expected_return_at' => now()->addDays(3),
            'returned_at' => null,
            'status' => 'active',
            'loaned_by' => User::factory(),
            'returned_to' => null,
            'notes' => fake()->optional()->sentence(),
            'return_notes' => null,
        ];
    }
}
