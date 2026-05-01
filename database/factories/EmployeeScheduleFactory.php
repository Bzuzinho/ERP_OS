<?php

namespace Database\Factories;

use App\Models\EmployeeSchedule;
use App\Models\Organization;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeScheduleFactory extends Factory
{
    protected $model = EmployeeSchedule::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'employee_id' => Employee::factory(),
            'weekday' => $this->faker->numberBetween(0, 6),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'valid_from' => now(),
            'valid_to' => null,
            'is_active' => true,
            'notes' => null,
        ];
    }
}
