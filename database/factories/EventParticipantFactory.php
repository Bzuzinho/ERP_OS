<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventParticipant>
 */
class EventParticipantFactory extends Factory
{
    protected $model = EventParticipant::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => null,
            'contact_id' => null,
            'role' => fake()->optional()->jobTitle(),
            'attendance_status' => 'invited',
        ];
    }
}
