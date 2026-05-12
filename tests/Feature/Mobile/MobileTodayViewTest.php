<?php

namespace Tests\Feature\Mobile;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Task;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class MobileTodayViewTest extends TestCase
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

    public function test_authenticated_user_can_access_today_view(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        $this->actingAs($user)
            ->get(route('mobile.today.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Mobile/Today/Index'));
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('mobile.today.index'))
            ->assertRedirect(route('login'));
    }

    public function test_today_view_shows_tasks_for_today(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'due_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.today.index'))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Today/Index')
            ->has('tasksForToday')
        );
    }

    public function test_today_view_shows_reopened_tasks(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'reopened',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.today.index'))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Today/Index')
            ->has('reopenedTasks')
        );
    }

    public function test_mobile_root_redirects_to_today(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);

        $this->actingAs($user)
            ->get(route('mobile.index'))
            ->assertRedirect(route('mobile.today.index'));
    }

    public function test_today_view_does_not_show_tasks_from_other_organizations(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view']);
        $otherUser = $this->makeAdminWithPermissions([]); // different org

        Task::factory()->create([
            'organization_id' => $otherUser->organization_id,
            'assigned_to' => $otherUser->id,
            'due_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)
            ->get(route('mobile.today.index'))
            ->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Mobile/Today/Index')
            ->where('tasksForToday', fn ($tasks) => count($tasks) === 0)
        );
    }
}
