<?php

namespace Database\Factories;

use App\Models\TeamMember;
use App\Models\Team;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'employee_id' => Employee::factory(),
            'role' => $this->faker->randomElement(['lead', 'member', 'support']),
            'joined_at' => $this->faker->dateTime(),
            'left_at' => null,
            'is_active' => true,
        ];
    }
}
