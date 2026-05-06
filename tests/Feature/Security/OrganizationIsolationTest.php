<?php

namespace Tests\Feature\Security;

use App\Models\Contact;
use App\Models\Document;
use App\Models\Event;
use App\Models\MeetingMinute;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\OperationalPlan;
use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class OrganizationIsolationTest extends TestCase
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

    public function test_admin_from_organization_a_does_not_see_tickets_from_organization_b_in_index(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organizationA);

        Ticket::factory()->create([
            'organization_id' => $organizationA->id,
            'created_by' => $admin->id,
            'title' => 'Ticket Org A Visivel',
        ]);

        Ticket::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Ticket Org B Oculto',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tickets.index'));

        $response->assertOk();
        $response->assertSee('Ticket Org A Visivel');
        $response->assertDontSee('Ticket Org B Oculto');
    }

    public function test_admin_from_organization_a_cannot_open_ticket_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organizationA);

        $foreignTicket = Ticket::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tickets.show', $foreignTicket))
            ->assertForbidden();
    }

    public function test_admin_from_organization_a_cannot_create_ticket_with_contact_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $admin = $this->makeAdminWithPermissions(['tickets.create'], $organizationA);
        $foreignContact = Contact::factory()->create(['organization_id' => $organizationB->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.tickets.create'))
            ->post(route('admin.tickets.store'), [
                'contact_id' => $foreignContact->id,
                'priority' => 'normal',
                'title' => 'Ticket invalido',
                'description' => 'Descricao',
                'source' => 'internal',
                'visibility' => 'internal',
            ]);

        $response->assertSessionHasErrors('contact_id');
    }

    public function test_admin_from_organization_a_cannot_assign_ticket_to_user_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $admin = $this->makeAdminWithPermissions(['tickets.create'], $organizationA);
        $foreignUser = User::factory()->create(['organization_id' => $organizationB->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.tickets.create'))
            ->post(route('admin.tickets.store'), [
                'assigned_to' => $foreignUser->id,
                'priority' => 'normal',
                'title' => 'Ticket invalido assigned',
                'description' => 'Descricao',
                'source' => 'internal',
                'visibility' => 'internal',
            ]);

        $response->assertSessionHasErrors('assigned_to');
    }

    public function test_portal_user_from_organization_a_does_not_see_documents_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        Document::factory()->create([
            'organization_id' => $organizationA->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $organizationA->id])->id,
            'title' => 'Documento Org A',
            'visibility' => 'public',
        ]);

        Document::factory()->create([
            'organization_id' => $organizationB->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Documento Org B',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($portalUser)->get(route('portal.documents.index'));

        $response->assertOk();
        $response->assertSee('Documento Org A');
        $response->assertDontSee('Documento Org B');
    }

    public function test_portal_user_from_organization_a_does_not_see_events_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        Event::factory()->create([
            'organization_id' => $organizationA->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationA->id])->id,
            'title' => 'Evento Org A',
            'visibility' => 'public',
        ]);

        Event::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Evento Org B',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($portalUser)->get(route('portal.events.index'));

        $response->assertOk();
        $response->assertSee('Evento Org A');
        $response->assertDontSee('Evento Org B');
    }

    public function test_portal_user_from_organization_a_does_not_see_spaces_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        Space::factory()->create([
            'organization_id' => $organizationA->id,
            'name' => 'Espaco Org A',
            'is_public' => true,
            'is_active' => true,
            'status' => 'available',
        ]);

        Space::factory()->create([
            'organization_id' => $organizationB->id,
            'name' => 'Espaco Org B',
            'is_public' => true,
            'is_active' => true,
            'status' => 'available',
        ]);

        $response = $this->actingAs($portalUser)->get(route('portal.spaces.index'));

        $response->assertOk();
        $response->assertSee('Espaco Org A');
        $response->assertDontSee('Espaco Org B');
    }

    public function test_portal_user_from_organization_a_does_not_see_meeting_minutes_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        $documentA = Document::factory()->create([
            'organization_id' => $organizationA->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $organizationA->id])->id,
            'title' => 'Ata Doc A',
            'visibility' => 'public',
        ]);
        $documentB = Document::factory()->create([
            'organization_id' => $organizationB->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Ata Doc B',
            'visibility' => 'public',
        ]);

        MeetingMinute::factory()->withDocument($documentA)->approved(User::factory()->create(['organization_id' => $organizationA->id]))->create([
            'organization_id' => $organizationA->id,
            'title' => 'Ata Org A',
            'event_id' => Event::factory()->create(['organization_id' => $organizationA->id, 'created_by' => User::factory()->create(['organization_id' => $organizationA->id])->id])->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationA->id])->id,
        ]);

        MeetingMinute::factory()->withDocument($documentB)->approved(User::factory()->create(['organization_id' => $organizationB->id]))->create([
            'organization_id' => $organizationB->id,
            'title' => 'Ata Org B',
            'event_id' => Event::factory()->create(['organization_id' => $organizationB->id, 'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id])->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
        ]);

        $response = $this->actingAs($portalUser)->get(route('portal.meeting-minutes.index'));

        $response->assertOk();
        $response->assertDontSee('Ata Org B');
    }

    public function test_portal_user_from_organization_a_does_not_see_operational_plans_from_organization_b(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        OperationalPlan::factory()->create([
            'organization_id' => $organizationA->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationA->id])->id,
            'owner_user_id' => User::factory()->create(['organization_id' => $organizationA->id])->id,
            'title' => 'Plano Org A',
            'visibility' => 'public',
            'status' => 'approved',
            'department_id' => null,
            'team_id' => null,
        ]);

        OperationalPlan::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'owner_user_id' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Plano Org B',
            'visibility' => 'public',
            'status' => 'approved',
            'department_id' => null,
            'team_id' => null,
        ]);

        $response = $this->actingAs($portalUser)->get(route('portal.operational-plans.index'));

        $response->assertOk();
        $response->assertSee('Plano Org A');
        $response->assertDontSee('Plano Org B');
    }

    public function test_portal_user_from_organization_a_cannot_open_ticket_from_other_user_or_organization(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);
        $otherUser = $this->makePortalUser('cidadao', $organizationB);

        $foreignTicket = Ticket::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => $otherUser->id,
            'title' => 'Ticket Outro Utilizador',
        ]);

        $this->actingAs($portalUser)
            ->get(route('portal.tickets.show', $foreignTicket))
            ->assertForbidden();
    }

    public function test_document_download_from_other_organization_is_blocked(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);

        $foreignDocument = Document::factory()->create([
            'organization_id' => $organizationB->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($portalUser)
            ->get(route('portal.documents.download', $foreignDocument));

        $this->assertContains($response->getStatusCode(), [403, 404]);
    }

    public function test_notification_recipient_from_other_user_cannot_be_marked_as_read(): void
    {
        [$organizationA] = $this->makeOrganizations();
        $portalUser = $this->makePortalUser('cidadao', $organizationA);
        $otherUser = $this->makePortalUser('cidadao', $organizationA);

        $notification = Notification::query()->create([
            'organization_id' => $organizationA->id,
            'type' => 'system',
            'title' => 'Teste notificacao',
            'message' => 'Mensagem',
            'priority' => 'normal',
            'created_by' => $otherUser->id,
        ]);

        $recipient = NotificationRecipient::query()->create([
            'notification_id' => $notification->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($portalUser)
            ->post(route('portal.notifications.mark-read', $recipient))
            ->assertForbidden();
    }

    public function test_super_admin_keeps_expected_cross_organization_access(): void
    {
        [$organizationA, $organizationB] = $this->makeOrganizations();
        $superAdmin = $this->makeSuperAdmin($organizationA);

        $foreignTicket = Ticket::factory()->create([
            'organization_id' => $organizationB->id,
            'created_by' => User::factory()->create(['organization_id' => $organizationB->id])->id,
            'title' => 'Ticket Cross Org Super Admin',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.tickets.show', $foreignTicket))
            ->assertOk()
            ->assertSee('Ticket Cross Org Super Admin');
    }

    /**
     * @return array{0: Organization, 1: Organization}
     */
    private function makeOrganizations(): array
    {
        return [
            Organization::factory()->create(['name' => 'Organizacao A']),
            Organization::factory()->create(['name' => 'Organizacao B']),
        ];
    }
}