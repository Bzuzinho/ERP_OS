<?php

namespace Tests\Feature\Tasks;

use App\Models\Task;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TaskValidationFlowTest extends TestCase
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

    public function test_task_can_be_submitted_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.submit-validation']);
        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.submit-validation', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending_validation',
        ]);
    }

    public function test_user_without_submit_validation_permission_cannot_submit_task(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.submit-validation', $task))
            ->assertForbidden();
    }

    public function test_user_with_validate_permission_can_validate_task_and_store_fields(): void
    {
        $validator = $this->makeAdminWithPermissions(['tasks.validate']);
        $task = Task::factory()->create([
            'organization_id' => $validator->organization_id,
            'created_by' => $validator->id,
            'status' => 'pending_validation',
            'validation_notes' => null,
            'validated_by' => null,
            'validated_at' => null,
        ]);

        $this->actingAs($validator)
            ->post(route('admin.tasks.validate', $task), [
                'validation_notes' => 'Checklist validada e aceite.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'validated',
            'validated_by' => $validator->id,
            'validation_notes' => 'Checklist validada e aceite.',
        ]);

        $this->assertNotNull($task->fresh()->validated_at);
    }

    public function test_user_without_validate_permission_cannot_validate_task(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'pending_validation',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.validate', $task), [
                'validation_notes' => 'Sem permissao.',
            ])
            ->assertForbidden();
    }

    public function test_legacy_done_status_remains_compatible_for_ticket_validation_readiness(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.submit-validation']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->count(2)->create([
            'status' => 'done',
        ]);

        $service = app(\App\Services\Tickets\TicketResolutionService::class);

        $this->assertTrue($service->canSubmitForValidation($ticket));
    }
}
