<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringOperationRun>
 */
class RecurringOperationRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recurring_operation_id' => RecurringOperation::factory(),
            'run_at' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'status' => fake()->randomElement(RecurringOperationRun::STATUSES),
            'generated_task_id' => fake()->boolean(50) ? Task::factory() : null,
            'generated_event_id' => fake()->boolean(50) ? Event::factory() : null,
            'error_message' => null,
            'executed_by' => fake()->boolean(50) ? User::factory() : null,
            'executed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
