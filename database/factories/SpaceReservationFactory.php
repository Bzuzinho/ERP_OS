<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceReservation>
 */
class SpaceReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = $this->faker->dateTimeBetween('+1 day', '+15 days');
        $endAt = (clone $startAt)->modify('+2 hours');

        return [
            'organization_id' => null,
            'space_id' => Space::factory(),
            'requested_by_user_id' => User::factory(),
            'contact_id' => Contact::factory(),
            'event_id' => null,
            'status' => $this->faker->randomElement(SpaceReservation::STATUSES),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'purpose' => $this->faker->sentence(6),
            'notes' => $this->faker->optional()->sentence(),
            'internal_notes' => $this->faker->optional()->sentence(),
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }
}
