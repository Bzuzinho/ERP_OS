<?php

namespace Tests\Feature\Sprint4A;

use App\Data\DashboardContext;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Dashboard\OperationalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardAccessScopeTest extends TestCase
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

    // ─── Cross-org isolation ──────────────────────────────────────────────────

    public function test_utilizador_nao_ve_dados_de_org_sem_acesso(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);

        Task::factory()->create(['organization_id' => $orgB->id, 'status' => 'in_progress']);

        $context = new DashboardContext($user, $orgA->id, null, null, false);
        $service = app(OperationalDashboardService::class);
        $tasks   = $service->getTarefasEmCurso($context);

        $this->assertCount(0, $tasks);
    }

    // ─── Pending reservation stays in orgA scope ──────────────────────────────

    public function test_alertas_reservas_pendentes_limitadas_a_org_do_contexto(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $userA = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($userA, $orgA);

        Ticket::factory()->create(['organization_id' => $orgA->id, 'status' => 'aguarda_validacao', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgB->id, 'status' => 'aguarda_validacao', 'priority' => 'normal']);

        $context = new DashboardContext($userA, $orgA->id, null, null, false);
        $service = app(OperationalDashboardService::class);
        $alertas = $service->getAlertas($context);

        $this->assertSame(1, $alertas['counts']['awaiting_validation_tickets']);
    }

    // ─── allMyScopes isolates across multiple orgs ────────────────────────────

    public function test_all_my_scopes_nao_inclui_orgs_sem_acesso(): void
    {
        $orgA       = Organization::factory()->create();
        $orgB       = Organization::factory()->create();
        $orgForeign = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);
        $this->insertOrgUser($user, $orgB);

        Task::factory()->create(['organization_id' => $orgA->id,       'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $orgB->id,       'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $orgForeign->id, 'status' => 'pending_validation']);

        $context = new DashboardContext($user, null, null, null, allMyScopes: true);
        $service = app(OperationalDashboardService::class);
        $tasks   = $service->getTarefasPorValidar($context);

        $this->assertCount(2, $tasks);
    }

    // ─── Explicit org context overrides allMyScopes ───────────────────────────

    public function test_contexto_org_explicito_sobrepoe_all_my_scopes(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);
        $this->insertOrgUser($user, $orgB);

        Task::factory()->create(['organization_id' => $orgA->id, 'status' => 'in_progress']);
        Task::factory()->create(['organization_id' => $orgB->id, 'status' => 'in_progress']);

        // Context scoped to orgA only (allMyScopes is false)
        $context = new DashboardContext($user, $orgA->id, null, null, allMyScopes: false);
        $service = app(OperationalDashboardService::class);
        $tasks   = $service->getTarefasEmCurso($context);

        $this->assertCount(1, $tasks);
    }

    // ─── getResumo aggregates correctly for all_my_scopes ────────────────────

    public function test_resumo_all_my_scopes_agrega_todas_as_orgs_acessiveis(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);
        $this->insertOrgUser($user, $orgA);
        $this->insertOrgUser($user, $orgB);

        Ticket::factory()->create(['organization_id' => $orgA->id, 'status' => 'em_analise',  'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgB->id, 'status' => 'em_execucao', 'priority' => 'normal']);

        $context = new DashboardContext($user, null, null, null, allMyScopes: true);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(2, $resumo['open_tickets']);
    }

    // ─── Empty scope returns zero data ────────────────────────────────────────

    public function test_user_sem_orgs_retorna_dados_vazios(): void
    {
        $user = User::factory()->create(['organization_id' => null]);
        // No pivot entries

        $context = new DashboardContext($user, null, null, null, allMyScopes: true);
        $service = app(OperationalDashboardService::class);
        $resumo  = $service->getResumo($context);

        $this->assertSame(0, $resumo['open_tickets']);
        $this->assertSame(0, $resumo['tasks_in_progress']);
        $this->assertSame(0, $resumo['tasks_pending_validation']);
    }
}
