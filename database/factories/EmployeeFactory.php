<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => null,
            'department_id' => Department::factory(),
            'employee_number' => 'EMP-' . $this->faker->unique()->numerify('####'),
            'role_title' => $this->faker->jobTitle(),
            'employment_type' => $this->faker->randomElement(['permanent', 'contract', 'temporary', 'volunteer', 'external', 'other']),
            'start_date' => $this->faker->dateTime(),
            'end_date' => null,
            'phone' => $this->faker->phoneNumber(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'notes' => null,
            'is_active' => true,
        ];
    }
}
