<?php

namespace Tests\Feature\Sprint4A;

use App\Data\DashboardContext;
use App\Models\Event;
use App\Models\OperationalPlan;
use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Dashboard\OperationalDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardCardsDataTest extends TestCase
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

    private function context(User $user, Organization $org): DashboardContext
    {
        return new DashboardContext($user, $org->id, null, null, false);
    }

    // ─── Agenda Hoje card ─────────────────────────────────────────────────────

    public function test_agenda_hoje_inclui_eventos_de_hoje(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Event::factory()->create([
            'organization_id' => $org->id,
            'start_at'        => now()->startOfDay()->addHours(9),
            'end_at'          => now()->startOfDay()->addHours(10),
            'status'          => 'scheduled',
        ]);
        Event::factory()->create([
            'organization_id' => $org->id,
            'start_at'        => now()->addDay()->startOfDay()->addHours(9),
            'end_at'          => now()->addDay()->startOfDay()->addHours(10),
            'status'          => 'scheduled',
        ]);

        $service = app(OperationalDashboardService::class);
        $agenda  = $service->getAgendaHoje($this->context($user, $org));

        $this->assertCount(1, $agenda['events']);
    }

    public function test_agenda_hoje_inclui_reservas_de_hoje(): void
    {
        $org   = Organization::factory()->create();
        $user  = User::factory()->create(['organization_id' => $org->id]);
        $space = Space::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        SpaceReservation::factory()->create([
            'organization_id'     => $org->id,
            'space_id'            => $space->id,
            'start_at'            => now()->startOfDay()->addHours(14),
            'end_at'              => now()->startOfDay()->addHours(16),
            'status'              => 'approved',
        ]);

        $service = app(OperationalDashboardService::class);
        $agenda  = $service->getAgendaHoje($this->context($user, $org));

        $this->assertCount(1, $agenda['reservations']);
    }

    // ─── Espaços card ─────────────────────────────────────────────────────────

    public function test_espacos_conta_reservas_hoje(): void
    {
        $org   = Organization::factory()->create();
        $user  = User::factory()->create(['organization_id' => $org->id]);
        $space = Space::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        SpaceReservation::factory()->create([
            'organization_id' => $org->id,
            'space_id'        => $space->id,
            'start_at'        => now()->startOfDay()->addHours(10),
            'end_at'          => now()->startOfDay()->addHours(11),
            'status'          => 'approved',
        ]);

        $service = app(OperationalDashboardService::class);
        $espacos = $service->getEspacos($this->context($user, $org));

        $this->assertGreaterThan(0, $espacos['counts']['today_total']);
    }

    public function test_espacos_conta_reservas_pendentes(): void
    {
        $org   = Organization::factory()->create();
        $user  = User::factory()->create(['organization_id' => $org->id]);
        $space = Space::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        SpaceReservation::factory()->create([
            'organization_id' => $org->id,
            'space_id'        => $space->id,
            'start_at'        => now()->addDay()->addHours(10),
            'end_at'          => now()->addDay()->addHours(11),
            'status'          => 'requested',
        ]);

        $service = app(OperationalDashboardService::class);
        $espacos = $service->getEspacos($this->context($user, $org));

        $this->assertGreaterThan(0, $espacos['counts']['pending_approval']);
    }

    // ─── Pedidos Recentes card ────────────────────────────────────────────────

    public function test_pedidos_recentes_retorna_tickets_abertos(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Ticket::factory()->count(3)->create([
            'organization_id' => $org->id,
            'status'          => 'em_analise',
            'priority'        => 'normal',
        ]);
        Ticket::factory()->create([
            'organization_id' => $org->id,
            'status'          => 'fechado',
            'priority'        => 'normal',
        ]);

        $service = app(OperationalDashboardService::class);
        $tickets = $service->getPedidosRecentes($this->context($user, $org));

        // Should not include closed tickets, max LIST_LIMIT (10)
        $this->assertLessThanOrEqual(10, count($tickets));
        foreach ($tickets as $t) {
            $this->assertNotSame('fechado', $t['status']);
        }
    }

    // ─── Planos Operacionais card ─────────────────────────────────────────────

    public function test_planos_operacionais_retorna_apenas_em_curso_ou_agendados(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        OperationalPlan::factory()->create(['organization_id' => $org->id, 'status' => 'in_progress']);
        OperationalPlan::factory()->create(['organization_id' => $org->id, 'status' => 'scheduled']);
        OperationalPlan::factory()->create(['organization_id' => $org->id, 'status' => 'draft']);
        OperationalPlan::factory()->create(['organization_id' => $org->id, 'status' => 'completed']);

        $service = app(OperationalDashboardService::class);
        $plans   = $service->getPlanosOperacionais($this->context($user, $org));

        foreach ($plans as $p) {
            $this->assertContains($p['status'], ['in_progress', 'scheduled', 'approved']);
        }
    }

    // ─── Próximas Atividades card ─────────────────────────────────────────────

    public function test_proximas_atividades_nao_inclui_passado(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        // Future event
        Event::factory()->create([
            'organization_id' => $org->id,
            'start_at'        => now()->addDays(2),
            'end_at'          => now()->addDays(2)->addHour(),
            'status'          => 'scheduled',
        ]);
        // Past event
        Event::factory()->create([
            'organization_id' => $org->id,
            'start_at'        => now()->subDays(2),
            'end_at'          => now()->subDays(2)->addHour(),
            'status'          => 'completed',
        ]);

        $service    = app(OperationalDashboardService::class);
        $atividades = $service->getProximasAtividades($this->context($user, $org));

        foreach ($atividades as $a) {
            $this->assertTrue(strtotime($a['start_at']) >= strtotime('now'));
        }
    }

    // ─── LIST_LIMIT enforcement ───────────────────────────────────────────────

    public function test_get_pedidos_recentes_respects_list_limit(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);
        $this->insertOrgUser($user, $org);

        Ticket::factory()->count(20)->create([
            'organization_id' => $org->id,
            'status'          => 'novo',
            'priority'        => 'normal',
        ]);

        $service = app(OperationalDashboardService::class);
        $tickets = $service->getPedidosRecentes($this->context($user, $org));

        $this->assertLessThanOrEqual(10, count($tickets));
    }
}
