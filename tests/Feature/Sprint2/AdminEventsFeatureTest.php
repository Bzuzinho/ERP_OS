<?php

namespace Tests\Feature\Sprint2;

use App\Models\Contact;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class AdminEventsFeatureTest extends TestCase
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

    public function test_admin_without_events_view_permission_cannot_list_events(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.access');

        $response = $this->actingAs($admin)->get(route('admin.events.index'));

        $response->assertForbidden();
    }

    public function test_user_with_events_view_permission_can_list_events(): void
    {
        $admin = $this->makeAdminWithPermissions(['events.view']);
        Event::factory()->count(2)->create(['organization_id' => $admin->organization_id, 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.events.index'));

        $response->assertOk();
    }

    public function test_user_with_events_create_can_create_event_linked_to_ticket_and_contact(): void
    {
        $admin = $this->makeAdminWithPermissions(['events.create']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $contact = Contact::factory()->create([
            'organization_id' => $admin->organization_id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Atendimento presencial sobre limpeza urbana',
            'description' => 'Reuniao com morador para validar novo plano de recolha.',
            'event_type' => 'appointment',
            'status' => 'scheduled',
            'start_at' => now()->addDay()->setHour(10)->toDateTimeString(),
            'end_at' => now()->addDay()->setHour(11)->toDateTimeString(),
            'location_text' => 'Edificio da Junta',
            'related_ticket_id' => $ticket->id,
            'related_contact_id' => $contact->id,
            'visibility' => 'restricted',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title' => 'Atendimento presencial sobre limpeza urbana',
            'related_ticket_id' => $ticket->id,
            'related_contact_id' => $contact->id,
            'created_by' => $admin->id,
        ]);
    }
}
