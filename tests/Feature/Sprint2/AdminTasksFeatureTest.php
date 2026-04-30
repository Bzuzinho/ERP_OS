<?php

namespace Tests\Feature\Sprint2;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class AdminTasksFeatureTest extends TestCase
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

    public function test_admin_without_tasks_view_permission_cannot_list_tasks(): void
    {
        $admin = $this->makePortalUser('cidadao');
        $admin->givePermissionTo('admin.access');

        $response = $this->actingAs($admin)->get(route('admin.tasks.index'));

        $response->assertForbidden();
    }

    public function test_user_with_tasks_view_permission_can_list_tasks(): void
    {
        $admin = $this->makeAdminWithPermissions(['tasks.view']);
        Task::factory()->count(2)->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.tasks.index'));

        $response->assertOk();
    }

    public function test_user_with_tasks_create_can_create_task_and_link_ticket(): void
    {
        $admin = $this->makeAdminWithPermissions(['tasks.create']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'ticket_id' => $ticket->id,
            'title' => 'Contactar fornecedor de manutencao',
            'description' => 'Agendar visita tecnica para o elevador.',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Contactar fornecedor de manutencao',
            'ticket_id' => $ticket->id,
            'created_by' => $admin->id,
        ]);
    }

    public function test_user_with_tasks_complete_can_complete_task_and_store_completion_fields(): void
    {
        $admin = $this->makeAdminWithPermissions(['tasks.complete']);
        $task = Task::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tasks.complete', $task));

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
            'completed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'task.completed',
        ]);
    }

    public function test_user_with_tasks_update_can_change_status_and_create_activity_log(): void
    {
        $admin = $this->makeAdminWithPermissions(['tasks.update']);
        $task = Task::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tasks.status.update', $task), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);

        $this->assertTrue(ActivityLog::query()->where('subject_type', Task::class)->where('subject_id', $task->id)->where('action', 'task.status_updated')->exists());
    }
}
