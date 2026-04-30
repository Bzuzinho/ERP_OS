<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskChecklist>
 */
class TaskChecklistFactory extends Factory
{
    protected $model = TaskChecklist::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => fake()->sentence(3),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
