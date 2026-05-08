<?php

namespace Tests\Feature\Sprint4A;

use App\Data\DashboardContext;
use App\Models\Department;
use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Dashboard\OperationalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardContextFilteringTest extends TestCase
{
    use RefreshDatabase;

    private function insertOrgUser(User $user, Organization $org, bool $hasGlobalAccess = false): void
    {
        DB::table('organization_user')->insertOrIgnore([
            'organization_id'   => $org->id,
            'user_id'           => $user->id,
            'role_context'      => null,
            'has_global_access' => $hasGlobalAccess,
            'is_default'        => true,
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    // Scenario 1: Global access user sees all departments/themes of the org
    public function test_presidente_com_global_access_ve_todos_os_dados_da_org(): void
    {
        $org  = Organization::factory()->create();
        $dept = Department::factory()->create(['organization_id' => $org->id, 'is_active' => true]);
        $area = ServiceArea::factory()->create(['organization_id' => $org->id, 'department_id' => $dept->id, 'is_active' => true]);

        $president = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($president, $org, hasGlobalAccess: true);

        // Tickets in different dept/area combinations
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal', 'department_id' => $dept->id, 'service_area_id' => $area->id]);
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal', 'department_id' => null, 'service_area_id' => null]);

        $context = new DashboardContext($president, $org->id, null, null, false);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(2, $resumo['open_tickets']);
    }

    // Scenario 2: Limited user only sees allowed dept data
    public function test_utilizador_limitado_ve_apenas_departamento_permitido(): void
    {
        $org   = Organization::factory()->create();
        $deptA = Department::factory()->create(['organization_id' => $org->id, 'is_active' => true]);
        $deptB = Department::factory()->create(['organization_id' => $org->id, 'is_active' => true]);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org, hasGlobalAccess: false);

        DB::table('department_user')->insert([
            'organization_id' => $org->id,
            'department_id'   => $deptA->id,
            'user_id'         => $user->id,
            'is_active'       => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal', 'department_id' => $deptA->id]);
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal', 'department_id' => $deptB->id]);

        // Context filtered to deptA
        $context = new DashboardContext($user, $org->id, $deptA->id, null, false);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(1, $resumo['open_tickets']);
    }

    // Scenario 3: all_my_scopes aggregates only accessible orgs
    public function test_all_my_scopes_agrega_apenas_escopos_acessiveis(): void
    {
        $orgA        = Organization::factory()->create();
        $orgB        = Organization::factory()->create();
        $orgBlocked  = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);
        $this->insertOrgUser($user, $orgB);
        // orgBlocked NOT linked to user

        Ticket::factory()->create(['organization_id' => $orgA->id,       'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgB->id,       'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgBlocked->id, 'status' => 'novo', 'priority' => 'normal']);

        $context = new DashboardContext($user, null, null, null, allMyScopes: true);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(2, $resumo['open_tickets']);
    }

    // Scenario 4: Data from another org does NOT appear
    public function test_dados_de_outra_organizacao_nao_aparecem(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);
        // NOT linked to orgB

        Ticket::factory()->create(['organization_id' => $orgA->id, 'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgB->id, 'status' => 'novo', 'priority' => 'normal']);

        $context = new DashboardContext($user, $orgA->id, null, null, false);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(1, $resumo['open_tickets']);
    }

    // Scenario 11: Legacy admin with organization_id still loads dashboard
    public function test_admin_legado_com_organization_id_carrega_dashboard(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        // user has organization_id but NO organization_user pivot

        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal']);

        $context = new DashboardContext($user, $org->id, null, null, false);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        // Should work — org context is explicit
        $this->assertSame(1, $resumo['open_tickets']);
    }
}
