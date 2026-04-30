<?php

namespace Tests\Feature\Sprint11;

use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class AdminTicketsFeatureTest extends TestCase
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

    public function test_super_admin_can_list_tickets(): void
    {
        $admin = $this->makeSuperAdmin();
        Ticket::factory()->count(2)->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAs($admin)->get(route('admin.tickets.index'));

        $response->assertOk();
    }

    public function test_user_with_tickets_create_can_create_ticket(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create']);

        $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido de limpeza urbana',
            'description' => 'Existem residuos acumulados na rua central.',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
        ]);

        $response->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();
        $this->assertSame($admin->id, $ticket->created_by);
        $this->assertNotEmpty($ticket->reference);
    }

    public function test_ticket_reference_uses_organization_code_when_available(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create']);
        $admin->organization->update(['code' => 'TST']);

        $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido com codigo',
            'description' => 'Descricao do pedido',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
        ]);

        $ticket = Ticket::query()->firstOrFail();

        $this->assertMatchesRegularExpression('/^TST-'.now()->year.'-000001$/', $ticket->reference);
    }

    public function test_ticket_reference_uses_fallback_when_organization_has_no_code(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create']);
        $admin->organization->update(['code' => null]);

        $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido sem codigo',
            'description' => 'Descricao do pedido',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
        ]);

        $ticket = Ticket::query()->firstOrFail();

        $this->assertMatchesRegularExpression('/^JF-'.now()->year.'-000001$/', $ticket->reference);
    }

    public function test_tickets_update_can_change_basic_data_and_create_activity_log(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.update']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.tickets.update', $ticket), [
            'title' => 'Titulo atualizado',
            'description' => 'Descricao atualizada',
            'priority' => 'high',
            'source' => 'internal',
            'visibility' => 'internal',
            'contact_id' => null,
            'assigned_to' => null,
            'department_id' => null,
            'category' => 'servicos',
            'subcategory' => 'limpeza',
            'location_text' => 'Largo da igreja',
            'due_date' => null,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Titulo atualizado',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Ticket::class,
            'subject_id' => $ticket->id,
            'action' => 'ticket.updated',
        ]);
    }

    public function test_tickets_assign_can_assign_responsible_and_log_activity(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.assign']);
        $assignee = User::factory()->create(['organization_id' => $admin->organization_id]);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tickets.assign', $ticket), [
            'assigned_to' => $assignee->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $assignee->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Ticket::class,
            'subject_id' => $ticket->id,
            'action' => 'ticket.assigned',
        ]);
    }

    public function test_tickets_close_permission_can_close_ticket_and_create_history_and_activity(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.close']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'novo',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tickets.status.update', $ticket), [
            'status' => 'fechado',
            'notes' => 'Resolvido e encerrado.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'fechado',
            'closed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'old_status' => 'novo',
            'new_status' => 'fechado',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Ticket::class,
            'subject_id' => $ticket->id,
            'action' => 'ticket.status_updated',
        ]);

        $this->assertTrue(TicketStatusHistory::query()->where('ticket_id', $ticket->id)->exists());
        $this->assertTrue(ActivityLog::query()->where('subject_type', Ticket::class)->where('subject_id', $ticket->id)->exists());
    }
}
