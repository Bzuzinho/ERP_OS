<?php

namespace Tests\Feature\Sprint4;

use App\Models\Contact;
use App\Models\Event;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceMaintenanceRecord;
use App\Models\SpaceReservation;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SpacesFeatureTest extends TestCase
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

    public function test_super_admin_consegue_listar_spaces(): void
    {
        $admin = $this->makeSuperAdmin();
        Space::factory()->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAs($admin)->get(route('admin.spaces.index'));

        $response->assertOk();
    }

    public function test_utilizador_sem_spaces_view_nao_consegue_listar_spaces(): void
    {
        $org = \App\Models\Organization::factory()->create();
        $user = \App\Models\User::factory()->create(['organization_id' => $org->id]);
        $user->givePermissionTo('admin.access'); // only admin.access, NO spaces.view

        $response = $this->actingAs($user)->get(route('admin.spaces.index'));

        $response->assertForbidden();
    }

    public function test_utilizador_com_spaces_create_consegue_criar_space(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.create']);

        $response = $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Sala de Testes',
            'status' => 'available',
            'is_public' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('spaces', [
            'organization_id' => $admin->organization_id,
            'name' => 'Sala de Testes',
        ]);
    }

    public function test_portal_user_consegue_ver_spaces_publicos_ativos(): void
    {
        $user = $this->makePortalUser();

        Space::factory()->create([
            'organization_id' => $user->organization_id,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('portal.spaces.index'));

        $response->assertOk();
    }

    public function test_portal_user_consegue_criar_pedido_reserva(): void
    {
        $user = $this->makePortalUser();
        $contact = Contact::factory()->forUser($user)->create();
        $space = Space::factory()->create([
            'organization_id' => $user->organization_id,
            'is_public' => true,
            'is_active' => true,
            'requires_approval' => true,
        ]);

        $response = $this->actingAs($user)->post(route('portal.space-reservations.store'), [
            'space_id' => $space->id,
            'contact_id' => $contact->id,
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'purpose' => 'Reuniao de associacao local',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('space_reservations', ['space_id' => $space->id, 'status' => 'requested']);
    }

    public function test_reserva_com_conflito_approved_nao_pode_ser_aprovada(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.approve_reservation']);
        $space = Space::factory()->create(['organization_id' => $admin->organization_id]);

        SpaceReservation::factory()->create([
            'organization_id' => $admin->organization_id,
            'space_id' => $space->id,
            'status' => 'approved',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHours(2),
        ]);

        $pending = SpaceReservation::factory()->create([
            'organization_id' => $admin->organization_id,
            'space_id' => $space->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->addMinutes(30),
            'end_at' => now()->addDay()->addHours(3),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.space-reservations.approve', $pending), [
            'notes' => 'aprovar',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('space_reservations', ['id' => $pending->id, 'status' => 'requested']);
    }

    public function test_admin_com_spaces_approve_reservation_consegue_aprovar_e_criar_registos(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.approve_reservation']);
        $space = Space::factory()->create([
            'organization_id' => $admin->organization_id,
            'has_cleaning_required' => true,
        ]);

        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $admin->organization_id,
            'space_id' => $space->id,
            'status' => 'requested',
            'event_id' => null,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHours(2),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.space-reservations.approve', $reservation), [
            'notes' => 'ok',
        ]);

        $response->assertRedirect();

        $reservation->refresh();
        $this->assertSame('approved', $reservation->status);
        $this->assertNotNull($reservation->event_id);
        $this->assertDatabaseHas('space_reservation_approvals', ['space_reservation_id' => $reservation->id, 'action' => 'approved']);
        $this->assertDatabaseHas('events', ['id' => $reservation->event_id, 'event_type' => 'reservation']);
        $this->assertDatabaseHas('space_cleaning_records', ['space_reservation_id' => $reservation->id]);
    }

    public function test_rejeicao_muda_status_para_rejected_e_guarda_rejection_reason(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.approve_reservation']);
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $admin->organization_id,
            'status' => 'requested',
        ]);

        $this->actingAs($admin)->post(route('admin.space-reservations.reject', $reservation), [
            'rejection_reason' => 'Conflito de agenda',
        ])->assertRedirect();

        $this->assertDatabaseHas('space_reservations', [
            'id' => $reservation->id,
            'status' => 'rejected',
            'rejection_reason' => 'Conflito de agenda',
        ]);
    }

    public function test_cancelamento_muda_status_para_cancelled(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.cancel_reservation']);
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $admin->organization_id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)->post(route('admin.space-reservations.cancel', $reservation), [
            'cancellation_reason' => 'Imprevisto',
        ])->assertRedirect();

        $this->assertDatabaseHas('space_reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_portal_user_so_ve_as_suas_reservas(): void
    {
        $user = $this->makePortalUser();
        $other = $this->makePortalUser();
        $contact = Contact::factory()->forUser($user)->create();

        SpaceReservation::factory()->create([
            'organization_id' => $user->organization_id,
            'requested_by_user_id' => $user->id,
            'contact_id' => $contact->id,
        ]);

        $otherReservation = SpaceReservation::factory()->create([
            'organization_id' => $other->organization_id,
            'requested_by_user_id' => $other->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.space-reservations.show', $otherReservation));

        $response->assertForbidden();
    }

    public function test_manutencao_pode_ser_criada_e_alterada_estado(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.manage_maintenance']);
        $space = Space::factory()->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAs($admin)->post(route('admin.space-maintenance.store'), [
            'space_id' => $space->id,
            'title' => 'Inspecao eletrica',
            'type' => 'inspection',
            'status' => 'pending',
        ]);

        $response->assertRedirect();

        $record = SpaceMaintenanceRecord::query()->latest()->firstOrFail();

        $this->actingAs($admin)->patch(route('admin.space-maintenance.status.update', $record), [
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('space_maintenance_records', [
            'id' => $record->id,
            'status' => 'completed',
            'completed_by' => $admin->id,
        ]);
    }

    public function test_completar_limpeza_preenche_completed_at_e_completed_by(): void
    {
        $admin = $this->makeAdminWithPermissions(['spaces.manage_cleaning']);
        $cleaning = SpaceCleaningRecord::factory()->create([
            'organization_id' => $admin->organization_id,
            'status' => 'in_progress',
            'completed_by' => null,
            'completed_at' => null,
        ]);

        $this->actingAs($admin)->post(route('admin.space-cleaning.complete', $cleaning), [
            'notes' => 'ok',
        ])->assertRedirect();

        $this->assertDatabaseHas('space_cleaning_records', [
            'id' => $cleaning->id,
            'status' => 'completed',
            'completed_by' => $admin->id,
        ]);
    }
}
