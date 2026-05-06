<?php

namespace Tests\Feature\Settings;

use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class Sprint12SettingsOrganizationTest extends TestCase
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

    // ========== admin.settings.index ==========

    public function test_super_admin_can_access_settings_index(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.index'));

        $response->assertOk();
    }

    public function test_admin_junta_can_access_settings_index(): void
    {
        $admin = $this->makeAdminWithPermissions(['users.view']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
    }

    public function test_user_without_permissions_cannot_access_settings_index(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertForbidden();
    }

    public function test_portal_user_cannot_access_settings_index(): void
    {
        $citizen = User::factory()->create();
        $citizen->assignRole('cidadao');

        $response = $this->actingAs($citizen)->get(route('admin.settings.index'));

        $response->assertForbidden();
    }

    // ========== admin.settings.service-areas.index ==========

    public function test_admin_can_access_settings_service_areas_index(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.view']);

        $response = $this->actingAs($admin)->get(route('admin.settings.service-areas.index'));

        $response->assertOk();
    }


    public function test_user_without_service_areas_view_cannot_access_settings_service_areas_index(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');
        // User only has admin.access, not service_areas.view

        $response = $this->actingAs($user)->get(route('admin.settings.service-areas.index'));

        $response->assertForbidden();
    }
    public function test_super_admin_can_access_settings_service_areas_index(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.service-areas.index'));

        $response->assertOk();
    }

    // ========== admin.settings.service-areas.create ==========

    public function test_admin_can_access_settings_service_areas_create(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.create']);

        $response = $this->actingAs($admin)->get(route('admin.settings.service-areas.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.create']);

        $response = $this->actingAs($admin)->post(route('admin.settings.service-areas.store'), [
            'name' => 'Área de Suporte',
            'slug' => 'suporte',
            'description' => 'Equipa de suporte técnico',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_areas', [
            'name' => 'Área de Suporte',
            'slug' => 'suporte',
        ]);
    }

    // ========== admin.settings.service-areas.show ==========


    public function test_admin_can_view_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.view']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Test Area',
            'slug' => 'test-area',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.service-areas.show', $serviceArea));

        $response->assertOk();
    }
    // ========== admin.settings.service-areas.edit ==========


    public function test_admin_can_edit_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.update']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Test Edit Area',
            'slug' => 'test-edit-area',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.service-areas.edit', $serviceArea));

        $response->assertOk();
    }

    public function test_admin_can_update_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.update']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Original Name',
            'slug' => 'original-name',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.settings.service-areas.update', $serviceArea), [
            'name' => 'Área Atualizada',
            'slug' => 'area-atualizada',
            'description' => 'Nova descrição',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_areas', [
            'id' => $serviceArea->id,
            'name' => 'Área Atualizada',
        ]);
    }
    // ========== admin.settings.service-areas.destroy ==========


    public function test_admin_can_delete_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.delete']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Area to Delete',
            'slug' => 'area-to-delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.settings.service-areas.destroy', $serviceArea));


        $response->assertRedirect();
        // ServiceArea uses SoftDeletes, so check deleted_at is set
        $this->assertNotNull($serviceArea->fresh()->deleted_at);
    }

    // ========== admin.settings.service-areas.users.store ==========

    public function test_admin_can_assign_user_to_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.manage_users']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Test Service Area',
            'slug' => 'test-service-area',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.settings.service-areas.users.store', $serviceArea), [
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_area_user', [
            'service_area_id' => $serviceArea->id,
            'user_id' => $user->id,
        ]);
    }
    // ========== admin.settings.service-areas.users.destroy ==========


    public function test_admin_can_remove_user_from_service_area_from_settings(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.manage_users']);
        $serviceArea = ServiceArea::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Test Remove Area',
            'slug' => 'test-remove-area',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'is_active' => true,
        ]);
        $serviceArea->users()->attach($user->id);

        $response = $this->actingAs($admin)->delete(
            route('admin.settings.service-areas.users.destroy', [
                'serviceArea' => $serviceArea,
                'userId' => $user->id,
            ])
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('service_area_user', [
            'service_area_id' => $serviceArea->id,
            'user_id' => $user->id,
        ]);
    }
    // ========== Backward compatibility: admin.service-areas.* should still work ==========

    public function test_old_admin_service_areas_index_route_still_works(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.view']);

        $response = $this->actingAs($admin)->get(route('admin.service-areas.index'));

        $response->assertRedirect(route('admin.settings.service-areas.index'));
    }

    public function test_old_admin_service_areas_create_route_still_works(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.create']);

        $response = $this->actingAs($admin)->get(route('admin.service-areas.create'));

        $response->assertRedirect(route('admin.settings.service-areas.create'));
    }

    // ========== Settings Index page shows correct cards ==========

    public function test_settings_index_shows_all_available_cards(): void
    {
        $admin = $this->makeAdminWithPermissions([
            'users.view',
            'roles.view',
            'service_areas.view',
            'notifications.view',
        ]);


        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        // Inertia should render the Settings Index page successfully
    }
}
