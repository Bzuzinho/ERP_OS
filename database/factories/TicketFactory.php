<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'reference' => strtoupper(fake()->lexify('TCK')).'-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'created_by' => User::factory(),
            'contact_id' => null,
            'assigned_to' => null,
            'department_id' => null,
            'category' => fake()->optional()->word(),
            'subcategory' => fake()->optional()->word(),
            'priority' => 'normal',
            'status' => 'novo',
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'location_text' => fake()->optional()->address(),
            'source' => 'internal',
            'visibility' => 'internal',
            'due_date' => null,
            'closed_at' => null,
            'closed_by' => null,
        ];
    }
}
