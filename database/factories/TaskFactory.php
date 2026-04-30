<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'ticket_id' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => 'pending',
            'priority' => 'normal',
            'start_date' => null,
            'due_date' => fake()->optional()->date(),
            'completed_at' => null,
            'completed_by' => null,
        ];
    }

    public function forTicket(Ticket $ticket): static
    {
        return $this->state(fn () => [
            'ticket_id' => $ticket->id,
            'organization_id' => $ticket->organization_id,
            'created_by' => $ticket->created_by,
        ]);
    }
}
