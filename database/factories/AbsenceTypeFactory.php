<?php

namespace Database\Factories;

use App\Models\AbsenceType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AbsenceTypeFactory extends Factory
{
    protected $model = AbsenceType::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->word(),
            'slug' => Str::slug($this->faker->unique()->word()),
            'description' => $this->faker->sentence(),
            'requires_approval' => $this->faker->boolean(),
            'is_paid' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
