<?php

namespace Tests\Feature\Sprint14;

use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class NavigationStructureTest extends TestCase
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

    public function test_portal_tasks_routes_are_not_registered(): void
    {
        $portalTaskRoutes = collect(Route::getRoutes()->getRoutesByName())
            ->keys()
            ->filter(fn (string $name) => str_starts_with($name, 'portal.tasks.'));

        $this->assertCount(0, $portalTaskRoutes);
    }

    public function test_portal_layout_does_not_reference_nonexistent_or_internal_modules(): void
    {
        $layoutContents = file_get_contents(resource_path('js/Layouts/PortalLayout.tsx'));

        $this->assertNotFalse($layoutContents);
        $this->assertStringNotContainsString('portal.tasks', $layoutContents);
        $this->assertStringNotContainsString("label: 'Tarefas'", $layoutContents);
        $this->assertStringNotContainsString('portal.operational-plans', $layoutContents);
    }

    public function test_super_admin_can_access_admin_settings_index(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->actingAs($superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_admin_settings_service_areas_index_works(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.view']);

        $this->actingAs($admin)
            ->get(route('admin.settings.service-areas.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ServiceAreas/Index')
            );
    }

    public function test_legacy_admin_service_areas_index_redirects_to_canonical_route(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.view']);

        $this->actingAs($admin)
            ->get(route('admin.service-areas.index'))
            ->assertRedirect(route('admin.settings.service-areas.index'));
    }

    public function test_portal_user_cannot_access_admin_settings(): void
    {
        $portalUser = $this->makePortalUser('cidadao');

        $this->actingAs($portalUser)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_user_with_settings_related_permission_receives_access_settings_flag(): void
    {
        $admin = $this->makeAdminWithPermissions(['notifications.view']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.can.accessSettings', true)
            );
    }
}
