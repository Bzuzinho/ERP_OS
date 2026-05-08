<?php

namespace Tests\Feature\SpaceReservations;

use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceMaintenanceRecord;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SpaceMaintenanceTicketCreationTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $organization;

    private User $manager;

    private User $withoutPermission;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->organization = Organization::factory()->create();

        $this->manager = $this->makeAdminWithPermissions([
            'spaces.view',
            'spaces.create-maintenance-ticket',
            'spaces.manage_maintenance',
            'tickets.create',
        ], $this->organization);

        $this->withoutPermission = $this->makePortalUser('cidadao', $this->organization);

        $this->space = Space::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'available',
        ]);
    }

    public function test_space_can_create_maintenance_ticket_and_link_record(): void
    {
        $response = $this->actingAs($this->manager)->post(route('admin.spaces.maintenance-ticket.store', $this->space), [
            'title' => 'Fuga na canalizacao',
            'description' => 'Existe fuga de agua na zona lateral.',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('admin.spaces.show', $this->space));

        $ticket = Ticket::query()->where('title', 'Fuga na canalizacao')->first();

        $this->assertNotNull($ticket);
        $this->assertEquals('maintenance', $ticket->type);
        $this->assertEquals($this->organization->id, $ticket->organization_id);
        $this->assertEquals('internal', $ticket->source);

        $record = SpaceMaintenanceRecord::query()->where('ticket_id', $ticket->id)->first();

        $this->assertNotNull($record);
        $this->assertEquals($this->space->id, $record->space_id);
        $this->assertEquals($this->organization->id, $record->organization_id);
        $this->assertEquals('maintenance', $record->type);
    }

    public function test_user_without_permission_cannot_create_maintenance_ticket_from_space(): void
    {
        $response = $this->actingAs($this->withoutPermission)->post(route('admin.spaces.maintenance-ticket.store', $this->space), [
            'title' => 'Porta danificada',
            'description' => 'A porta principal nao fecha corretamente.',
        ]);

        $response->assertStatus(403);
    }
}
