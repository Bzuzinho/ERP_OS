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

class MobileMyTasksTest extends TestCase
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

    public function test_operational_user_can_access_tasks_index(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        $this->actingAs($user)
            ->get(route('mobile.tasks.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Mobile/Tasks/Index'));
    }

    public function test_operational_user_sees_only_assigned_tasks(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);
        $otherUser = $this->makeAdminWithPermissions(['tasks.view']);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'title' => 'My assigned task',
        ]);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $otherUser->id,
            'title' => 'Other user task',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.tasks.index'))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Tasks/Index')
            ->where('tasks.data', fn ($tasks) => count($tasks) === 1
                && $tasks[0]['title'] === 'My assigned task')
        );
    }

    public function test_operational_user_cannot_see_tasks_from_other_organizations(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);
        $otherOrg = Organization::factory()->create();
        $otherOrgUser = $this->makeAdminWithPermissions(['tasks.view'], $otherOrg);

        Task::factory()->create([
            'organization_id' => $otherOrg->id,
            'assigned_to' => $otherOrgUser->id,
            'title' => 'Cross org task',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.tasks.index'))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Tasks/Index')
            ->where('tasks.data', fn ($tasks) => count($tasks) === 0)
        );
    }

    public function test_operational_user_can_view_task_detail(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('mobile.tasks.show', $task))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Mobile/Tasks/Show'));
    }

    public function test_filter_shows_only_today_tasks(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'due_date' => now()->toDateString(),
            'title' => 'Today task',
        ]);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'title' => 'Future task',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.tasks.index', ['status' => 'today']))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Tasks/Index')
            ->where('tasks.data', fn ($tasks) => count($tasks) === 1
                && $tasks[0]['title'] === 'Today task')
        );
    }

    public function test_reopened_tasks_are_visible_with_filter(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'reopened',
            'title' => 'Reopened task',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.tasks.index', ['status' => 'reopened']))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Tasks/Index')
            ->where('tasks.data', fn ($tasks) => count($tasks) === 1
                && $tasks[0]['title'] === 'Reopened task')
        );
    }
}
