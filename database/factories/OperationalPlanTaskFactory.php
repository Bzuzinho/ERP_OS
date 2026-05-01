<?php

namespace Database\Factories;

use App\Models\OperationalPlan;
use App\Models\OperationalPlanTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalPlanTask>
 */
class OperationalPlanTaskFactory extends Factory
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
            'task_id' => Task::factory(),
            'position' => fake()->numberBetween(0, 10),
            'is_milestone' => fake()->boolean(20),
            'weight' => fake()->optional()->numberBetween(1, 100),
        ];
    }
}
