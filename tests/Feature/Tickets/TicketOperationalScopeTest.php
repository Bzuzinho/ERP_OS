<?php

namespace Tests\Feature\Tickets;

use App\Models\Department;
use App\Models\Organization;
use App\Models\ServiceArea;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketOperationalScopeTest extends TestCase
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

    public function test_user_with_global_organization_access_can_create_occurrence_in_any_department_and_service_area(): void
    {
        $organization = Organization::factory()->create();
        $department = Department::factory()->create(['organization_id' => $organization->id]);
        $serviceArea = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $department->id,
        ]);

        $user = $this->makeAdminWithPermissions(['tickets.create'], $organization);

        DB::table('organization_user')->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_context' => null,
            'has_global_access' => true,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.store'), [
                'organization_id' => $organization->id,
                'department_id' => $department->id,
                'service_area_id' => $serviceArea->id,
                'type' => 'occurrence',
                'title' => 'Ocorrencia global',
                'description' => 'Teste de contexto operacional com acesso global.',
                'priority' => 'normal',
                'source' => 'internal',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'organization_id' => $organization->id,
            'department_id' => $department->id,
            'service_area_id' => $serviceArea->id,
        ]);
    }

    public function test_user_without_global_access_can_only_create_ticket_in_allowed_department_and_service_area(): void
    {
        $organization = Organization::factory()->create();

        $allowedDepartment = Department::factory()->create(['organization_id' => $organization->id]);
        $blockedDepartment = Department::factory()->create(['organization_id' => $organization->id]);

        $allowedServiceArea = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $allowedDepartment->id,
        ]);

        $blockedServiceArea = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $blockedDepartment->id,
        ]);

        $user = $this->makeAdminWithPermissions(['tickets.create'], $organization);

        DB::table('organization_user')->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_context' => null,
            'has_global_access' => false,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('department_user')->insert([
            'organization_id' => $organization->id,
            'department_id' => $allowedDepartment->id,
            'user_id' => $user->id,
            'role_context' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_area_user')->insert([
            'service_area_id' => $allowedServiceArea->id,
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'role' => null,
            'role_context' => null,
            'is_primary' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.store'), [
                'organization_id' => $organization->id,
                'department_id' => $allowedDepartment->id,
                'service_area_id' => $allowedServiceArea->id,
                'title' => 'Ticket permitido',
                'description' => 'Criação no contexto permitido.',
                'priority' => 'normal',
                'source' => 'internal',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->from(route('admin.tickets.create'))
            ->post(route('admin.tickets.store'), [
                'organization_id' => $organization->id,
                'department_id' => $blockedDepartment->id,
                'service_area_id' => $blockedServiceArea->id,
                'title' => 'Ticket bloqueado',
                'description' => 'Não deve permitir criação fora do escopo.',
                'priority' => 'normal',
                'source' => 'internal',
                'visibility' => 'internal',
            ])
            ->assertSessionHasErrors(['department_id']);
    }

    public function test_user_cannot_create_ticket_in_organization_without_access(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        $departmentB = Department::factory()->create(['organization_id' => $organizationB->id]);
        $serviceAreaB = ServiceArea::factory()->create([
            'organization_id' => $organizationB->id,
            'department_id' => $departmentB->id,
        ]);

        $user = $this->makeAdminWithPermissions(['tickets.create'], $organizationA);

        $this->actingAs($user)
            ->from(route('admin.tickets.create'))
            ->post(route('admin.tickets.store'), [
                'organization_id' => $organizationB->id,
                'department_id' => $departmentB->id,
                'service_area_id' => $serviceAreaB->id,
                'title' => 'Tentativa cross organization',
                'description' => 'Não deve permitir criação sem acesso.',
                'priority' => 'normal',
                'source' => 'internal',
                'visibility' => 'internal',
            ])
            ->assertSessionHasErrors(['organization_id']);
    }
}
