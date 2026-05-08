<?php

namespace Tests\Feature\Tickets;

use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketValidationFlowTest extends TestCase
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

    public function test_ticket_cannot_move_to_aguarda_validacao_via_generic_status_route(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.update']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'em_analise',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.tickets.status.update', $ticket), [
                'status' => 'aguarda_validacao',
                'notes' => 'Encaminhado para validação operacional.',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'em_analise',
        ]);
    }

    public function test_validating_ticket_sets_resolved_and_validation_metadata(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.validate']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'aguarda_validacao',
            'validated_at' => null,
            'validated_by' => null,
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.validate', $ticket), [
                'validation_notes' => 'Conferido no terreno e validado.',
                'resolution_notes' => 'Sem pendências adicionais.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolvido',
            'validated_by' => $user->id,
            'validation_notes' => 'Conferido no terreno e validado.',
            'resolution_notes' => 'Sem pendências adicionais.',
        ]);

        $this->assertNotNull($ticket->fresh()->validated_at);
    }

    public function test_validating_ticket_creates_validation_notification(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.validate']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'status' => 'aguarda_validacao',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.validate', $ticket), [
                'validation_notes' => 'Conferido.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'type' => 'ticket_validated',
            'notifiable_type' => Ticket::class,
            'notifiable_id' => $ticket->id,
        ]);
    }

    public function test_cancel_ticket_keeps_data_and_updates_status(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.cancel']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'em_execucao',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.cancel', $ticket), [
                'notes' => 'Pedido cancelado por duplicação.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'cancelado',
            'resolution_notes' => 'Pedido cancelado por duplicação.',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'deleted_at' => null,
        ]);
    }
}
