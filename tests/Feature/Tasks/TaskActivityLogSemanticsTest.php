<?php

namespace Tests\Feature\Tasks;

use App\Models\ActivityLog;
use App\Models\Task;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TaskActivityLogSemanticsTest extends TestCase
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

    public function test_complete_endpoint_generates_task_completed_log(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.complete', $task))
            ->assertRedirect();

        $this->assertTrue(
            ActivityLog::query()
                ->where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->where('action', 'task.completed')
                ->exists()
        );
    }

    public function test_generic_status_update_generates_task_status_updated_log(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.update']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.tasks.status.update', $task), [
                'status' => 'in_progress',
            ])
            ->assertRedirect();

        $this->assertTrue(
            ActivityLog::query()
                ->where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->where('action', 'task.status_updated')
                ->exists()
        );
    }
}
