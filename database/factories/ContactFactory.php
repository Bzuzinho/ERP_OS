<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => null,
            'type' => fake()->randomElement(Contact::TYPES),
            'name' => fake()->name(),
            'nif' => fake()->optional()->numerify('#########'),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('2########'),
            'mobile' => fake()->optional()->numerify('9########'),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
    }
}
