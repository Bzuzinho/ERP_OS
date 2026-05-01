<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\Organization;
use App\Models\Employee;
use App\Models\AbsenceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = $this->faker->dateTime();
        $end = (new \DateTime($start->format('Y-m-d')))->modify('+5 days');

        return [
            'organization_id' => Organization::factory(),
            'employee_id' => Employee::factory(),
            'absence_type_id' => null,
            'leave_type' => $this->faker->randomElement(['vacation', 'sick_leave', 'justified_absence', 'unpaid_leave', 'family_support', 'training', 'other']),
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => 6,
            'status' => 'requested',
            'reason' => $this->faker->sentence(),
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'created_by' => null,
        ];
    }
}
