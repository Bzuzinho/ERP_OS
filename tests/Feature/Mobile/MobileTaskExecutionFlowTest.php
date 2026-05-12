<?php

namespace Tests\Feature\Mobile;

use App\Models\Organization;
use App\Models\Task;
use App\Models\TaskChecklist;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class MobileTaskExecutionFlowTest extends TestCase
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

    public function test_operational_user_can_start_task(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.start', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_operational_user_without_permission_cannot_start_task(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('tasks.view'); // only view, not complete

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.start', $task))
            ->assertForbidden();
    }

    public function test_operational_user_can_complete_task(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.complete', $task))
            ->assertRedirect();

        // CompleteTaskAction transitions to pending_validation when workflow is enabled
        $task->refresh();
        $this->assertContains($task->status, ['done', 'pending_validation']);
    }

    public function test_operational_user_can_add_observation(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.observations.store', $task), [
                'observation' => 'Observação de teste da equipa terreno.',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertStringContainsString('Observação de teste da equipa terreno.', $task->observations);
    }

    public function test_operational_user_can_submit_task_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete', 'tasks.submit-validation']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.submit-validation', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending_validation',
        ]);
    }

    public function test_operational_user_without_submit_validation_permission_cannot_submit(): void
    {
        // Use a portal user with only tasks.view — no tasks.submit-validation
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'done',
        ]);

        $this->actingAs($user)
            ->post(route('mobile.tasks.submit-validation', $task))
            ->assertForbidden();
    }

    public function test_operational_user_without_validate_permission_cannot_validate_task(): void
    {
        // Portal user without tasks.validate permission
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo(['tasks.view', 'tasks.complete', 'tasks.submit-validation', 'admin.access']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'pending_validation',
        ]);

        // There's no mobile validate route (only admin), but checking policy enforcement
        $this->actingAs($user)
            ->post(route('admin.tasks.validate', $task), [
                'validation_notes' => 'Forcing validate without permission',
            ])
            ->assertForbidden();
    }

    public function test_operational_user_cannot_start_task_from_another_organization(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);
        $otherOrg = Organization::factory()->create();
        $otherOrgUser = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete'], $otherOrg);

        $task = Task::factory()->create([
            'organization_id' => $otherOrg->id,
            'assigned_to' => $otherOrgUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->post(route('mobile.tasks.start', $task));

        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_mobile_does_not_affect_admin_task_operations(): void
    {
        $admin = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $admin->organization_id,
            'assigned_to' => $admin->id,
            'status' => 'in_progress',
        ]);

        // Admin route still works
        $this->actingAs($admin)
            ->post(route('admin.tasks.complete', $task))
            ->assertRedirect();

        // CompleteTaskAction transitions to pending_validation when workflow is enabled
        $task->refresh();
        $this->assertContains($task->status, ['done', 'pending_validation']);
    }
}
