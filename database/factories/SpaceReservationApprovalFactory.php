<?php

namespace Database\Factories;

use App\Models\SpaceReservation;
use App\Models\SpaceReservationApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceReservationApproval>
 */
class SpaceReservationApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $newStatus = $this->faker->randomElement(['requested', 'approved', 'rejected', 'cancelled', 'completed']);

        return [
            'space_reservation_id' => SpaceReservation::factory(),
            'action' => $newStatus,
            'decided_by' => User::factory(),
            'notes' => $this->faker->optional()->sentence(),
            'old_status' => null,
            'new_status' => $newStatus,
        ];
    }
}
