<?php

namespace Tests\Feature\Tickets;

use App\Models\Notification;
use App\Models\Task;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketTaskCompletionSyncTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);
    }

    public function test_completing_last_task_makes_ticket_ready_for_validation_without_auto_transition(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.complete', 'tasks.update', 'tasks.validate', 'tickets.submit-validation']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'validated']);
        $task = Task::factory()->forTicket($ticket)->create(['status' => 'pending']);

        $this->actingAs($user)
            ->post(route('admin.tasks.complete', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending_validation',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'com_tarefas',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.validate', $task), [
                'validation_notes' => 'Validacao operacional concluida.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('admin.tickets.submit-validation', $ticket))
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'aguarda_validacao',
        ]);
    }

    public function test_reopening_task_moves_ticket_back_to_operational_status_and_notifies(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.update']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'status' => 'aguarda_validacao',
            'validated_at' => now(),
            'validated_by' => $user->id,
            'validation_notes' => 'Validado antes da reabertura.',
        ]);

        $task = Task::factory()->forTicket($ticket)->create([
            'status' => 'done',
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.tasks.status.update', $task), [
                'status' => 'in_progress',
            ])
            ->assertRedirect();

        $ticket = $ticket->fresh();

        $this->assertSame('em_execucao', $ticket->status);
        $this->assertNull($ticket->validated_at);
        $this->assertNull($ticket->validated_by);
        $this->assertNull($ticket->validation_notes);

        $notification = Notification::query()->where('type', 'ticket_reopened_from_tasks')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
        ]);
    }
}