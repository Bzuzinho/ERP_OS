<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company().' '.$this->faker->randomElement(['Sala', 'Auditorio', 'Centro']);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(12),
            'location_text' => $this->faker->streetName(),
            'capacity' => $this->faker->numberBetween(10, 250),
            'status' => $this->faker->randomElement(Space::STATUSES),
            'requires_approval' => $this->faker->boolean(70),
            'has_cleaning_required' => $this->faker->boolean(60),
            'has_deposit' => false,
            'deposit_amount' => null,
            'price' => null,
            'rules' => $this->faker->optional()->paragraph(),
            'is_public' => $this->faker->boolean(70),
            'is_active' => true,
        ];
    }
}
