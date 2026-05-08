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
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationalDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeContext(User $user, ?Organization $org = null, bool $allMyScopes = false): DashboardContext
    {
        return new DashboardContext(
            $user,
            $org?->id,
            null,
            null,
            $allMyScopes,
        );
    }

    private function insertOrgUser(User $user, Organization $org, bool $hasGlobalAccess = false): void
    {
        DB::table('organization_user')->insertOrIgnore([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role_context'    => null,
            'has_global_access' => $hasGlobalAccess,
            'is_default'      => true,
            'is_active'       => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ─── getResumo ────────────────────────────────────────────────────────────

    public function test_get_resumo_counts_open_tickets(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'em_analise', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'resolvido', 'priority' => 'normal']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $resumo = $service->getResumo($context);

        $this->assertSame(2, $resumo['open_tickets']);
    }

    public function test_get_resumo_counts_tasks_pending_validation(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'done']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $resumo = $service->getResumo($context);

        $this->assertSame(2, $resumo['tasks_pending_validation']);
    }

    public function test_get_resumo_counts_tasks_reopened(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'reopened']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'in_progress']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $resumo = $service->getResumo($context);

        $this->assertSame(1, $resumo['tasks_reopened']);
    }

    // ─── getAlertas ───────────────────────────────────────────────────────────

    public function test_get_alertas_includes_overdue_tasks(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'in_progress',
            'due_date'        => now()->subDays(3)->toDateString(),
        ]);
        Task::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'in_progress',
            'due_date'        => now()->addDays(3)->toDateString(),
        ]);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $alertas = $service->getAlertas($context);

        $this->assertSame(1, $alertas['counts']['overdue_tasks']);
    }

    public function test_get_alertas_includes_pending_validation_tasks(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'pending_validation']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $alertas = $service->getAlertas($context);

        $this->assertSame(2, $alertas['counts']['pending_validation_tasks']);
        $this->assertCount(2, $alertas['pending_validation_tasks']);
    }

    public function test_get_alertas_includes_awaiting_validation_tickets(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'aguarda_validacao', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $org->id, 'status' => 'novo', 'priority' => 'normal']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $alertas = $service->getAlertas($context);

        $this->assertSame(1, $alertas['counts']['awaiting_validation_tickets']);
    }

    public function test_get_alertas_includes_reopened_tasks(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'reopened']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $alertas = $service->getAlertas($context);

        $this->assertSame(1, $alertas['counts']['reopened_tasks']);
        $this->assertCount(1, $alertas['reopened_tasks']);
    }

    // ─── getTarefasPorValidar ─────────────────────────────────────────────────

    public function test_get_tarefas_por_validar_returns_only_pending_validation(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'pending_validation']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'in_progress']);
        Task::factory()->create(['organization_id' => $org->id, 'status' => 'done']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $tasks = $service->getTarefasPorValidar($context);

        $this->assertCount(1, $tasks);
        $this->assertSame('pending_validation', $tasks[0]['status']);
    }

    // ─── getTarefasEmCurso ────────────────────────────────────────────────────

    public function test_get_tarefas_em_curso_flags_overdue(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'in_progress',
            'due_date'        => now()->subDay()->toDateString(),
        ]);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $tasks = $service->getTarefasEmCurso($context);

        $this->assertCount(1, $tasks);
        $this->assertTrue($tasks[0]['is_overdue']);
    }

    public function test_get_tarefas_em_curso_includes_reopened(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create(['organization_id' => $org->id, 'status' => 'reopened']);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $tasks = $service->getTarefasEmCurso($context);

        $this->assertCount(1, $tasks);
        $this->assertSame('reopened', $tasks[0]['status']);
    }

    // ─── getAgendaHoje ────────────────────────────────────────────────────────

    public function test_get_agenda_hoje_returns_today_tasks(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Task::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'in_progress',
            'due_date'        => now()->toDateString(),
        ]);
        Task::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'in_progress',
            'due_date'        => now()->addDay()->toDateString(),
        ]);

        $context = $this->makeContext($user, $org);
        $service = app(OperationalDashboardService::class);
        $agenda = $service->getAgendaHoje($context);

        $this->assertCount(1, $agenda['tasks']);
    }

    // ─── Empty context ────────────────────────────────────────────────────────

    public function test_empty_org_ids_returns_zeros(): void
    {
        $user = User::factory()->create(['organization_id' => null]);
        // No org_user pivot — user has no org access
        $context = new DashboardContext($user, null, null, null, false);
        $service = app(OperationalDashboardService::class);

        $resumo = $service->getResumo($context);
        $this->assertSame(0, $resumo['open_tickets']);
        $this->assertSame(0, $resumo['tasks_in_progress']);
    }
}
