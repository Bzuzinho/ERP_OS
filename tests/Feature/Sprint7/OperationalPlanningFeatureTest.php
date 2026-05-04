<?php

namespace Tests\Feature\Sprint7;

use App\Models\OperationalPlan;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class OperationalPlanningFeatureTest extends TestCase
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

    public function test_super_admin_consegue_listar_operational_plans(): void
    {
        $admin = $this->makeSuperAdmin();
        OperationalPlan::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.operational-plans.index'))
            ->assertOk();
    }

    public function test_utilizador_sem_planning_view_nao_consegue_listar_operational_plans(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.operational-plans.index'))
            ->assertForbidden();
    }

    public function test_utilizador_com_planning_create_consegue_criar_plan_e_slug_automatico(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.create', 'planning.update']);

        $this->actingAs($admin)
            ->post(route('admin.operational-plans.store'), [
                'title' => 'Plano Teste Sprint 7',
                'plan_type' => 'activity',
                'visibility' => 'internal',
                'start_date' => now()->toDateString(),
                'status' => 'draft',
            ])
            ->assertRedirect();

        $plan = OperationalPlan::query()->where('title', 'Plano Teste Sprint 7')->firstOrFail();

        $this->assertNotEmpty($plan->slug);
        $this->assertSame($admin->id, (int) $plan->created_by);
    }

    public function test_aprovar_plan_preenche_campos_de_aprovacao(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.update', 'planning.approve']);
        $plan = OperationalPlan::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.operational-plans.approve', $plan))
            ->assertRedirect();

        $plan->refresh();
        $this->assertSame('approved', $plan->status);
        $this->assertNotNull($plan->approved_at);
        $this->assertSame($admin->id, (int) $plan->approved_by);
    }

    public function test_cancelar_plan_exige_cancellation_reason_e_muda_status(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.update', 'planning.cancel']);
        $plan = OperationalPlan::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.operational-plans.show', $plan))
            ->post(route('admin.operational-plans.cancel', $plan), [])
            ->assertSessionHasErrors('cancellation_reason');

        $this->actingAs($admin)
            ->post(route('admin.operational-plans.cancel', $plan), ['cancellation_reason' => 'Motivo teste'])
            ->assertRedirect();

        $plan->refresh();
        $this->assertSame('cancelled', $plan->status);
        $this->assertSame('Motivo teste', $plan->cancellation_reason);
    }

    public function test_concluir_plan_coloca_progress_percent_em_100(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.update', 'planning.complete']);
        $plan = OperationalPlan::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'progress_percent' => 45,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.operational-plans.complete', $plan))
            ->assertRedirect();

        $this->assertSame(100, (int) $plan->fresh()->progress_percent);
    }

    public function test_associar_task_a_plan_evita_duplicado(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.update', 'planning.manage_tasks']);
        $plan = OperationalPlan::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);
        $task = Task::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.operational-plans.tasks.store', $plan), ['task_id' => $task->id])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.operational-plans.tasks.store', $plan), ['task_id' => $task->id])->assertRedirect();

        $this->assertSame(1, $plan->fresh()->tasks()->whereKey($task->id)->count());
    }

    public function test_progresso_calcula_com_base_em_tasks_done_total(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.update', 'planning.manage_tasks']);
        $plan = OperationalPlan::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);

        $doneTask = Task::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id, 'status' => 'done']);
        $pendingTask = Task::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id, 'status' => 'pending']);

        $plan->tasks()->attach($doneTask->id);
        $plan->tasks()->attach($pendingTask->id);

        app(\App\Services\Planning\OperationalPlanProgressService::class)->recalculate($plan);

        $this->assertSame(50, (int) $plan->fresh()->progress_percent);
    }

    public function test_criar_recurring_operation_calcula_next_run_at(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.manage_recurring']);

        $this->actingAs($admin)
            ->post(route('admin.recurring-operations.store'), [
                'title' => 'Recorrência Teste',
                'operation_type' => 'task',
                'frequency' => 'weekly',
                'interval' => 1,
                'start_date' => now()->toDateString(),
                'task_template' => ['title' => 'Tarefa automática'],
            ])
            ->assertRedirect();

        $operation = RecurringOperation::query()->where('title', 'Recorrência Teste')->firstOrFail();
        $this->assertNotNull($operation->next_run_at);
    }

    public function test_pausar_recurring_operation_muda_status_para_paused(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.manage_recurring']);
        $operation = RecurringOperation::factory()->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id, 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.recurring-operations.pause', $operation))
            ->assertRedirect();

        $this->assertSame('paused', $operation->fresh()->status);
    }

    public function test_executar_recurring_operation_task_gera_task(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.manage_recurring', 'planning.execute_recurring']);
        $operation = RecurringOperation::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'operation_type' => 'task',
            'task_template' => ['title' => 'Tarefa recorrente gerada'],
            'status' => 'active',
        ]);
        $run = RecurringOperationRun::factory()->create([
            'recurring_operation_id' => $operation->id,
            'status' => 'pending',
            'generated_task_id' => null,
            'generated_event_id' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.recurring-operations.runs.execute', ['recurringOperation' => $operation->id, 'run' => $run->id]))
            ->assertRedirect();

        $this->assertNotNull($run->fresh()->generated_task_id);
    }

    public function test_executar_recurring_operation_event_gera_event(): void
    {
        $admin = $this->makeAdminWithPermissions(['planning.view', 'planning.manage_recurring', 'planning.execute_recurring']);
        $operation = RecurringOperation::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'operation_type' => 'event',
            'event_template' => [
                'title' => 'Evento recorrente gerado',
                'start_at' => now()->addDay()->toDateTimeString(),
                'end_at' => now()->addDay()->addHour()->toDateTimeString(),
            ],
            'status' => 'active',
        ]);
        $run = RecurringOperationRun::factory()->create([
            'recurring_operation_id' => $operation->id,
            'status' => 'pending',
            'generated_task_id' => null,
            'generated_event_id' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.recurring-operations.runs.execute', ['recurringOperation' => $operation->id, 'run' => $run->id]))
            ->assertRedirect();

        $this->assertNotNull($run->fresh()->generated_event_id);
    }

    public function test_portal_so_ve_planos_public_portal_com_status_permitido(): void
    {
        $user = $this->makePortalUser('cidadao');

        OperationalPlan::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => User::factory()->create(['organization_id' => $user->organization_id])->id,
            'title' => 'Plano Publico',
            'visibility' => 'public',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('portal.operational-plans.index'))
            ->assertOk()
            ->assertSee('Plano Publico');
    }

    public function test_portal_nao_ve_planos_internal_ou_restricted(): void
    {
        $user = $this->makePortalUser('cidadao');

        OperationalPlan::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => User::factory()->create(['organization_id' => $user->organization_id])->id,
            'title' => 'Plano Interno',
            'visibility' => 'internal',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('portal.operational-plans.index'))
            ->assertOk()
            ->assertDontSee('Plano Interno');
    }

    public function test_policies_bloqueiam_acesso_sem_permissoes(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $plan = OperationalPlan::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => User::factory()->create(['organization_id' => $user->organization_id])->id,
        ]);

        $this->actingAs($user)->get(route('admin.operational-plans.show', $plan))->assertOk();
        $this->actingAs($user)->get(route('admin.recurring-operations.index'))->assertForbidden();
    }
}
