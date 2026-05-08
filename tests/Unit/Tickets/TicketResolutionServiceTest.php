<?php

namespace Tests\Unit\Tickets;

use App\Models\Task;
use App\Models\Ticket;
use App\Services\Tickets\TicketResolutionService;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketResolutionServiceTest extends TestCase
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

    public function test_ticket_with_pending_task_is_not_ready_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'pending']);

        $service = app(TicketResolutionService::class);

        $this->assertFalse($service->canSubmitForValidation($ticket));
        $this->assertFalse($service->progress($ticket)['ready_for_validation']);
    }

    public function test_ticket_with_all_done_tasks_is_ready_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->count(2)->create(['status' => 'done']);

        $service = app(TicketResolutionService::class);

        $this->assertTrue($service->canSubmitForValidation($ticket));
    }

    public function test_cancelled_task_does_not_block_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'done']);
        Task::factory()->forTicket($ticket)->create(['status' => 'cancelled']);

        $service = app(TicketResolutionService::class);

        $this->assertTrue($service->canSubmitForValidation($ticket));
    }

    public function test_ticket_without_tasks_does_not_become_ready_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'em_analise',
        ]);

        $service = app(TicketResolutionService::class);

        $this->assertFalse($service->canSubmitForValidation($ticket));
        $this->assertSame(0, $service->progress($ticket)['total']);
    }
}