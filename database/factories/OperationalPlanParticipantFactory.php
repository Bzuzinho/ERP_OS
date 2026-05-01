<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\OperationalPlan;
use App\Models\OperationalPlanParticipant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalPlanParticipant>
 */
class OperationalPlanParticipantFactory extends Factory
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
            'user_id' => User::factory(),
            'employee_id' => fake()->boolean(50) ? Employee::factory() : null,
            'team_id' => fake()->boolean(50) ? Team::factory() : null,
            'role' => fake()->optional()->jobTitle(),
        ];
    }
}
