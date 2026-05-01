<?php

namespace Database\Factories;

use App\Models\EmployeeEventAssignment;
use App\Models\Employee;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeEventAssignmentFactory extends Factory
{
    protected $model = EmployeeEventAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'event_id' => Event::factory(),
            'assigned_by' => null,
            'role' => $this->faker->randomElement(['organizer', 'participant', 'observer']),
            'assigned_at' => now(),
            'removed_at' => null,
            'is_active' => true,
        ];
    }
}
