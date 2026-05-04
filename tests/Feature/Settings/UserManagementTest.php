<?php

namespace Tests\Feature\Settings;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    // ---------- settings.index ----------

    public function test_settings_index_requires_users_view(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertForbidden();
    }

    public function test_settings_index_accessible_with_users_view(): void
    {
        $admin = $this->makeAdminWithPermissions(['users.view']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
    }

    // ---------- users.index ----------

    public function test_super_admin_can_list_all_users(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.users.index'));

        $response->assertOk();
    }

    public function test_user_without_users_view_cannot_list_users(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');

        $response = $this->actingAs($user)->get(route('admin.settings.users.index'));

        $response->assertForbidden();
    }

    // ---------- users.create / store ----------

    public function test_admin_junta_can_create_normal_user(): void
    {
        $org = Organization::first();
        $admin = $this->makeAdminWithPermissions(['users.create']);

        $response = $this->actingAs($admin)->post(route('admin.settings.users.store'), [
            'name'            => 'Novo Utilizador',
            'email'           => 'novo@juntaos.local',
            'password'        => 'password123',
            'organization_id' => $org->id,
            'roles'           => ['operacional'],
            'is_active'       => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'novo@juntaos.local']);
    }

    public function test_admin_junta_cannot_assign_super_admin_role(): void
    {
        $admin = $this->makeAdminWithPermissions(['users.create', 'users.manage_roles']);

        // First create user
        $this->actingAs($admin)->post(route('admin.settings.users.store'), [
            'name'   => 'Teste Super',
            'email'  => 'teste.super@juntaos.local',
            'roles'  => ['operacional'],
        ]);

        $target = User::where('email', 'teste.super@juntaos.local')->first();
        $this->assertNotNull($target);

        // Now try assigning super_admin
        $response = $this->actingAs($admin)->post(
            route('admin.settings.users.update-roles', $target->id),
            ['roles' => ['super_admin']],
        );

        $response->assertSessionHasErrors(['roles']);
        $this->assertFalse($target->fresh()->hasRole('super_admin'));
    }

    public function test_super_admin_can_assign_super_admin_role(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $target = User::factory()->create(['organization_id' => $superAdmin->organization_id]);

        $response = $this->actingAs($superAdmin)->post(
            route('admin.settings.users.update-roles', $target->id),
            ['roles' => ['super_admin']],
        );

        $response->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole('super_admin'));
    }

    // ---------- deactivate ----------

    public function test_user_cannot_deactivate_themselves(): void
    {
        $admin = $this->makeAdminWithPermissions(['users.delete']);

        $response = $this->actingAs($admin)->post(route('admin.settings.users.deactivate', $admin->id));

        // Policy denies self-deactivation → 403
        $response->assertForbidden();
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_deactivate_another_user(): void
    {
        $org = Organization::first();
        $admin = $this->makeAdminWithPermissions(['users.delete']);
        $target = User::factory()->create(['organization_id' => $org->id, 'is_active' => true]);
        $target->assignRole('operacional');

        $response = $this->actingAs($admin)->post(route('admin.settings.users.deactivate', $target->id));

        $response->assertRedirect();
        $this->assertFalse($target->fresh()->is_active);
    }

    // ---------- reset-password ----------

    public function test_user_with_reset_password_permission_can_reset(): void
    {
        $admin = $this->makeAdminWithPermissions(['users.reset_password']);
        $target = User::factory()->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAs($admin)->post(
            route('admin.settings.users.reset-password', $target->id),
            ['password' => 'NewSecure123!', 'password_confirmation' => 'NewSecure123!'],
        );

        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewSecure123!', $target->fresh()->password));
    }

    public function test_user_without_reset_password_permission_cannot_reset(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.access');
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.settings.users.reset-password', $target->id),
            ['password' => 'NewSecure123!', 'password_confirmation' => 'NewSecure123!'],
        );

        $response->assertForbidden();
    }

    // ---------- inactive user ----------

    public function test_inactive_user_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->givePermissionTo('admin.access');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        // Should be redirected to login
        $response->assertRedirect(route('login'));
    }

    // ---------- roles.index ----------

    public function test_super_admin_role_cannot_be_edited_by_non_super_admin(): void
    {
        $admin = $this->makeAdminWithPermissions(['roles.update']);
        $superAdminRole = Role::findByName('super_admin', 'web');

        $response = $this->actingAs($admin)->put(
            route('admin.settings.roles.update', $superAdminRole->id),
            ['permissions' => ['admin.access']],
        );

        $response->assertForbidden();
    }

    public function test_super_admin_can_edit_super_admin_role(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $superAdminRole = Role::findByName('super_admin', 'web');

        $response = $this->actingAs($superAdmin)->put(
            route('admin.settings.roles.update', $superAdminRole->id),
            ['permissions' => ['admin.access', 'users.view']],
        );

        $response->assertRedirect();
    }
}
