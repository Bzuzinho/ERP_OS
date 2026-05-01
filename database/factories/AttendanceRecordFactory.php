<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Organization;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'employee_id' => Employee::factory(),
            'date' => $this->faker->dateTime(),
            'status' => $this->faker->randomElement(['present', 'absent', 'vacation', 'sick_leave', 'justified_absence', 'unjustified_absence', 'remote', 'off', 'training', 'overtime']),
            'check_in' => '09:00',
            'check_out' => '17:00',
            'worked_minutes' => 480,
            'source' => 'manual',
            'notes' => null,
            'validated_by' => null,
            'validated_at' => null,
            'created_by' => null,
        ];
    }
}
