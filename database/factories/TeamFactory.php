<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Organization;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'department_id' => Department::factory(),
            'name' => $this->faker->word(),
            'slug' => Str::slug($this->faker->unique()->word()),
            'description' => $this->faker->sentence(),
            'leader_user_id' => null,
            'is_active' => true,
        ];
    }
}
