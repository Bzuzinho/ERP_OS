<?php

namespace Tests\Feature\Tasks;

use App\Models\Task;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TaskTicketResolutionSyncTest extends TestCase
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

    public function test_reopened_task_blocks_ticket_from_final_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.submit-validation']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'validated']);
        Task::factory()->forTicket($ticket)->create(['status' => 'reopened']);

        $service = app(\App\Services\Tickets\TicketResolutionService::class);

        $this->assertFalse($service->canSubmitForValidation($ticket));
    }

    public function test_validated_tasks_allow_ticket_to_advance_to_final_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.submit-validation']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->count(2)->create(['status' => 'validated']);

        $service = app(\App\Services\Tickets\TicketResolutionService::class);

        $this->assertTrue($service->canSubmitForValidation($ticket));
    }

    public function test_cancelled_task_does_not_block_when_other_tasks_are_validated(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.submit-validation']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'validated']);
        Task::factory()->forTicket($ticket)->create(['status' => 'cancelled']);

        $service = app(\App\Services\Tickets\TicketResolutionService::class);

        $this->assertTrue($service->canSubmitForValidation($ticket));
    }

    public function test_portal_ticket_access_remains_stable_after_task_validation_workflow_actions(): void
    {
        $organization = \App\Models\Organization::query()->firstOrFail();

        $portalUser = $this->makePortalUser('cidadao', $organization);
        $admin = $this->makeAdminWithPermissions([
            'tasks.submit-validation',
            'tasks.validate',
        ], $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $portalUser->id,
            'status' => 'com_tarefas',
            'source' => 'portal',
            'type' => 'portal',
        ]);

        $task = Task::factory()->forTicket($ticket)->create([
            'organization_id' => $organization->id,
            'created_by' => $admin->id,
            'status' => 'done',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tasks.submit-validation', $task))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.tasks.validate', $task), [
                'validation_notes' => 'Validada sem bloqueios.',
            ])
            ->assertRedirect();

        $this->actingAs($portalUser)
            ->get(route('portal.tickets.show', $ticket))
            ->assertOk();
    }
}
