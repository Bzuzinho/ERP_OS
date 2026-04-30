<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\SpaceMaintenanceRecord;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpaceMaintenanceRecord>
 */
class SpaceMaintenanceRecordFactory extends Factory
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
            'ticket_id' => Ticket::factory(),
            'task_id' => Task::factory(),
            'type' => $this->faker->randomElement(SpaceMaintenanceRecord::TYPES),
            'status' => $this->faker->randomElement(SpaceMaintenanceRecord::STATUSES),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->optional()->sentence(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+15 days'),
            'completed_at' => null,
            'assigned_to' => User::factory(),
            'completed_by' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
