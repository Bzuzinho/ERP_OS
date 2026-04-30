<?php

namespace Database\Factories;

use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskChecklistItem>
 */
class TaskChecklistItemFactory extends Factory
{
    protected $model = TaskChecklistItem::class;

    public function definition(): array
    {
        return [
            'task_checklist_id' => TaskChecklist::factory(),
            'label' => fake()->sentence(2),
            'is_completed' => false,
            'completed_at' => null,
            'completed_by' => null,
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
