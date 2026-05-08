<?php

namespace Tests\Feature\Tickets;

use App\Models\Task;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketTaskGenerationTest extends TestCase
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

    public function test_generated_task_from_ticket_keeps_ticket_and_organization_context(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.generate-tasks']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'em_analise',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.generate-tasks', $ticket), [
                'tasks' => [[
                    'title' => 'Executar intervenção',
                    'description' => 'Criada a partir do pedido para execução operacional.',
                    'priority' => 'normal',
                ]],
            ])
            ->assertRedirect();

        $task = Task::query()->firstOrFail();

        $this->assertSame($ticket->id, $task->ticket_id);
        $this->assertSame($ticket->organization_id, $task->organization_id);
        $this->assertSame($user->id, $task->created_by);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'com_tarefas',
        ]);
    }
}
