<?php

namespace Tests\Feature\Tasks;

use App\Models\ActivityLog;
use App\Models\Task;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TaskReopenFlowTest extends TestCase
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

    public function test_reopening_task_increments_counter_and_clears_current_validation_fields(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.reopen']);
        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'validated',
            'validated_by' => $user->id,
            'validated_at' => now(),
            'validation_notes' => 'Validada na ronda anterior.',
            'reopen_count' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.reopen', $task), [
                'reason' => 'Necessita correção adicional.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'reopened',
            'reopen_count' => 2,
            'validated_by' => null,
            'validated_at' => null,
            'validation_notes' => null,
        ]);
    }

    public function test_reopening_task_creates_task_reopened_activity_log(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.reopen']);
        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'pending_validation',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tasks.reopen', $task), [
                'reason' => 'Checklist incompleta.',
                'target_status' => 'in_progress',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
            'reopen_count' => 1,
        ]);

        $this->assertTrue(
            ActivityLog::query()
                ->where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->where('action', 'task.reopened')
                ->exists()
        );
    }
}
