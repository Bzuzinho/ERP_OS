<?php

namespace Tests\Feature\Sprint18;

use App\Models\SpaceReservation;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SmokeDemoUxTest extends TestCase
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

    public function test_admin_dashboard_responde(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_portal_dashboard_responde(): void
    {
        $portal = $this->makePortalUser('cidadao');

        $this->actingAs($portal)
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    public function test_portal_criar_pedido_responde(): void
    {
        $portal = $this->makePortalUser('cidadao');

        $this->actingAs($portal)
            ->get(route('portal.tickets.create'))
            ->assertOk();
    }

    public function test_admin_tickets_show_responde(): void
    {
        $admin = $this->makeSuperAdmin();
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertOk();
    }

    public function test_portal_tickets_show_responde(): void
    {
        $portal = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $portal->organization_id,
            'created_by' => $portal->id,
        ]);

        $this->actingAs($portal)
            ->get(route('portal.tickets.show', $ticket))
            ->assertOk();
    }

    public function test_admin_reservations_index_responde(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.space-reservations.index'))
            ->assertOk();
    }

    public function test_portal_reservations_index_responde(): void
    {
        $portal = $this->makePortalUser('cidadao');
        SpaceReservation::factory()->create([
            'organization_id' => $portal->organization_id,
            'requested_by_user_id' => $portal->id,
        ]);

        $this->actingAs($portal)
            ->get(route('portal.space-reservations.index'))
            ->assertOk();
    }

    public function test_admin_notifications_index_responde(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk();
    }

    public function test_portal_notifications_index_responde(): void
    {
        $portal = $this->makePortalUser('cidadao');

        $this->actingAs($portal)
            ->get(route('portal.notifications.index'))
            ->assertOk();
    }

    public function test_settings_index_responde_para_admin(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_portal_user_nao_acede_settings_admin(): void
    {
        $portal = $this->makePortalUser('cidadao');

        $this->actingAs($portal)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_navegacao_mobile_portal_nao_expoe_modulos_internos(): void
    {
        $portal = $this->makePortalUser('cidadao');

        $this->actingAs($portal)
            ->get(route('portal.more.index'))
            ->assertOk()
            ->assertDontSee('Recursos Humanos')
            ->assertDontSee('Planeamento')
            ->assertDontSee('Relatorios');
    }
}
