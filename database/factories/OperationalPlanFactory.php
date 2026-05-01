<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\OperationalPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalPlan>
 */
class OperationalPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 weeks', '+2 weeks');

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'plan_type' => fake()->randomElement(OperationalPlan::TYPES),
            'status' => fake()->randomElement(OperationalPlan::STATUSES),
            'visibility' => fake()->randomElement(OperationalPlan::VISIBILITIES),
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+2 months'),
            'owner_user_id' => User::factory(),
            'department_id' => Department::factory(),
            'team_id' => Team::factory(),
            'budget_estimate' => fake()->optional()->randomFloat(2, 100, 50000),
            'progress_percent' => fake()->numberBetween(0, 100),
            'created_by' => User::factory(),
        ];
    }
}
