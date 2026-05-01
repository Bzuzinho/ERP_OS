<?php

namespace Database\Factories;

use App\Models\EmployeeTaskAssignment;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeTaskAssignmentFactory extends Factory
{
    protected $model = EmployeeTaskAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'task_id' => Task::factory(),
            'assigned_by' => null,
            'role' => $this->faker->randomElement(['executor', 'reviewer', 'support']),
            'assigned_at' => now(),
            'removed_at' => null,
            'is_active' => true,
        ];
    }
}
