<?php

namespace Tests\Feature\Tickets;

use App\Models\Contact;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketTypeAssignmentTest extends TestCase
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

    public function test_admin_can_create_ticket_with_occurrence_type(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create']);

        $this->actingAs($admin)
            ->post(route('admin.tickets.store'), [
                'title' => 'Ocorrencia de via publica',
                'description' => 'Sinalizacao danificada no cruzamento principal.',
                'priority' => 'normal',
                'source' => 'internal',
                'type' => 'occurrence',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'created_by' => $admin->id,
            'type' => 'occurrence',
        ]);
    }

    public function test_portal_flow_remains_compatible_with_portal_type(): void
    {
        $citizen = $this->makePortalUser('cidadao');
        $contact = Contact::factory()->forUser($citizen)->create();

        $this->actingAs($citizen)
            ->post(route('portal.tickets.store'), [
                'contact_id' => $contact->id,
                'title' => 'Pedido pelo portal',
                'description' => 'Preciso de apoio para recolha de monos.',
                'priority' => 'normal',
                'source' => 'portal',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();

        $this->assertSame('portal', $ticket->source);
        $this->assertSame('portal', $ticket->type);
    }
}
