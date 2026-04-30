<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceCleaningRecord>
 */
class SpaceCleaningRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => null,
            'space_id' => Space::factory(),
            'space_reservation_id' => SpaceReservation::factory(),
            'task_id' => Task::factory(),
            'status' => $this->faker->randomElement(SpaceCleaningRecord::STATUSES),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+15 days'),
            'completed_at' => null,
            'assigned_to' => User::factory(),
            'completed_by' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
