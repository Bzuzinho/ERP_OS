<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->word(),
            'slug' => Str::slug($this->faker->unique()->word()),
            'description' => $this->faker->sentence(),
            'manager_user_id' => null,
            'is_active' => true,
        ];
    }
}
