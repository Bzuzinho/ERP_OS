<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company().' '.fake()->citySuffix();

        return [
            'name' => $name,
            'code' => strtoupper(fake()->lexify('???')),
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(100, 999)),
            'nif' => fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('9########'),
            'address' => fake()->address(),
            'logo_path' => null,
            'is_active' => true,
        ];
    }

    public function withoutCode(): static
    {
        return $this->state(fn () => [
            'code' => null,
        ]);
    }
}
