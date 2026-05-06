<?php

namespace Tests\Feature\Sprint15;

use App\Models\Comment;
use App\Models\Document;
use App\Models\Event;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class PortalStabilizationTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $org;
    private User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->org = Organization::query()->first();
        $this->portalUser = $this->makePortalUser('cidadao', $this->org);
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function test_portal_dashboard_responds_for_portal_user(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.dashboard'));

        $response->assertOk();
    }

    public function test_portal_dashboard_does_not_include_operational_plans(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->missing('public_plans')
        );
    }

    // ── Tickets ───────────────────────────────────────────────────────────────

    public function test_portal_tickets_index_shows_only_own_tickets(): void
    {
        $own = Ticket::factory()->create([
            'organization_id' => $this->org->id,
            'created_by' => $this->portalUser->id,
            'visibility' => 'public',
            'source' => 'portal',
        ]);

        $other = Ticket::factory()->create([
            'organization_id' => $this->org->id,
            'visibility' => 'public',
            'source' => 'portal',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.tickets.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('tickets.data', 1)
                 ->where('tickets.data.0.id', $own->id)
        );
    }

    public function test_portal_ticket_show_hides_internal_comments(): void
    {
        $ticket = Ticket::factory()->create([
            'organization_id' => $this->org->id,
            'created_by' => $this->portalUser->id,
            'visibility' => 'public',
            'source' => 'portal',
        ]);

        Comment::create([
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'user_id' => $this->portalUser->id,
            'organization_id' => $this->org->id,
            'visibility' => 'internal',
            'body' => 'Internal note',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('ticket.comments', 0)
        );
    }

    public function test_portal_ticket_show_includes_public_comments(): void
    {
        $ticket = Ticket::factory()->create([
            'organization_id' => $this->org->id,
            'created_by' => $this->portalUser->id,
            'visibility' => 'public',
            'source' => 'portal',
        ]);

        Comment::create([
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'user_id' => $this->portalUser->id,
            'organization_id' => $this->org->id,
            'visibility' => 'public',
            'body' => 'Public reply',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('ticket.comments', 1)
        );
    }

    public function test_portal_ticket_show_does_not_expose_assigned_to(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $this->org);

        $ticket = Ticket::factory()->create([
            'organization_id' => $this->org->id,
            'created_by' => $this->portalUser->id,
            'assigned_to' => $admin->id,
            'visibility' => 'public',
            'source' => 'portal',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->missing('ticket.assigned_to')
        );
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    public function test_portal_documents_index_responds(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.documents.index'));

        $response->assertOk();
    }

    // ── Space Reservations ────────────────────────────────────────────────────

    public function test_portal_space_reservations_shows_only_own(): void
    {
        $space = Space::factory()->create(['organization_id' => $this->org->id]);

        $own = SpaceReservation::factory()->create([
            'organization_id' => $this->org->id,
            'space_id' => $space->id,
            'requested_by_user_id' => $this->portalUser->id,
        ]);

        SpaceReservation::factory()->create([
            'organization_id' => $this->org->id,
            'space_id' => $space->id,
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.space-reservations.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('reservations.data', 1)
                 ->where('reservations.data.0.id', $own->id)
        );
    }

    public function test_portal_space_reservation_show_does_not_expose_internal_notes(): void
    {
        $space = Space::factory()->create(['organization_id' => $this->org->id]);

        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org->id,
            'space_id' => $space->id,
            'requested_by_user_id' => $this->portalUser->id,
            'internal_notes' => 'Secret admin note',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.space-reservations.show', $reservation));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->missing('reservation.internal_notes')
        );
    }

    // ── Notifications ─────────────────────────────────────────────────────────

    public function test_portal_notifications_index_responds(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.notifications.index'));

        $response->assertOk();
    }

    // ── Admin routes blocked for portal users ────────────────────────────────

    public function test_portal_user_cannot_access_admin_tickets(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }
}
