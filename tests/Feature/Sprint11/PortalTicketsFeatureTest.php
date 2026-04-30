<?php

namespace Tests\Feature\Sprint11;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class PortalTicketsFeatureTest extends TestCase
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

    public function test_external_user_can_create_portal_ticket_with_source_and_owner(): void
    {
        $user = $this->makePortalUser('cidadao');
        $contact = Contact::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->post(route('portal.tickets.store'), [
            'contact_id' => $contact->id,
            'title' => 'Buraco na via publica',
            'description' => 'Existe um buraco junto ao cruzamento principal.',
            'priority' => 'normal',
            'source' => 'portal',
            'visibility' => 'internal',
        ]);

        $response->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();

        $this->assertSame('portal', $ticket->source);
        $this->assertSame($user->id, $ticket->created_by);
    }

    public function test_external_user_only_sees_own_tickets_in_portal_index(): void
    {
        $user = $this->makePortalUser('cidadao');
        $otherUser = $this->makePortalUser('cidadao', $user->organization);

        Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'title' => 'Meu pedido',
        ]);

        Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $otherUser->id,
            'title' => 'Pedido de outro utilizador',
        ]);

        $response = $this->actingAs($user)->get(route('portal.tickets.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Tickets/Index')
            ->has('tickets.data', 1)
            ->where('tickets.data.0.title', 'Meu pedido')
        );
    }

    public function test_external_user_cannot_view_other_users_ticket(): void
    {
        $user = $this->makePortalUser('cidadao');
        $otherUser = $this->makePortalUser('cidadao', $user->organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.tickets.show', $ticket));

        $response->assertForbidden();
    }

    public function test_external_user_cannot_change_ticket_status(): void
    {
        $user = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'novo',
        ]);

        $response = $this->actingAs($user)->patch(route('admin.tickets.status.update', $ticket), [
            'status' => 'fechado',
        ]);

        $response->assertForbidden();
    }

    public function test_external_user_does_not_see_internal_comments_and_only_public_attachments(): void
    {
        $user = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $user->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Interno',
            'visibility' => 'internal',
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $user->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Publico',
            'visibility' => 'public',
        ]);

        Attachment::query()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $user->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'file_path' => 'tickets/attachments/interno.txt',
            'file_name' => 'interno.txt',
            'visibility' => 'internal',
        ]);

        Attachment::query()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $user->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'file_path' => 'tickets/attachments/publico.txt',
            'file_name' => 'publico.txt',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('portal.tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Tickets/Show')
            ->has('ticket.comments', 1)
            ->where('ticket.comments.0.body', 'Publico')
            ->has('ticket.attachments', 1)
            ->where('ticket.attachments.0.file_name', 'publico.txt')
        );
    }
}
