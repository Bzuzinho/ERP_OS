<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(0, 10))->setTime(fake()->numberBetween(8, 17), 0);

        return [
            'organization_id' => Organization::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'event_type' => 'appointment',
            'status' => 'scheduled',
            'start_at' => $start,
            'end_at' => (clone $start)->addHour(),
            'location_text' => fake()->optional()->streetAddress(),
            'created_by' => User::factory(),
            'related_ticket_id' => null,
            'related_contact_id' => null,
            'visibility' => 'internal',
        ];
    }
}
