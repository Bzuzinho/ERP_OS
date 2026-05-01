<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\RecurringOperation;
use App\Models\Space;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringOperation>
 */
class RecurringOperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'operation_type' => fake()->randomElement(RecurringOperation::TYPES),
            'status' => fake()->randomElement(RecurringOperation::STATUSES),
            'frequency' => fake()->randomElement(RecurringOperation::FREQUENCIES),
            'interval' => fake()->numberBetween(1, 4),
            'weekdays' => ['monday'],
            'day_of_month' => fake()->optional()->numberBetween(1, 28),
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+6 months'),
            'next_run_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'last_run_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'owner_user_id' => User::factory(),
            'department_id' => Department::factory(),
            'team_id' => Team::factory(),
            'related_space_id' => Space::factory(),
            'task_template' => ['title' => fake()->sentence(3), 'priority' => 'normal'],
            'event_template' => null,
            'created_by' => User::factory(),
        ];
    }
}
