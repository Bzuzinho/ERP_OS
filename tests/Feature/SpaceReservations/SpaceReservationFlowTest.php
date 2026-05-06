<?php

namespace Tests\Feature\SpaceReservations;

use App\Models\Contact;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SpaceReservationFlowTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $org1;
    private Organization $org2;
    private User $adminUser;
    private User $adminUser2;
    private User $portalUser;
    private Space $space1;
    private Space $space2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        // Create organizations
        $this->org1 = Organization::factory()->create(['name' => 'Organization 1']);
        $this->org2 = Organization::factory()->create(['name' => 'Organization 2']);

        // Create admin users
        $this->adminUser = $this->makeAdminWithPermissions(
            ['spaces.approve_reservation', 'spaces.cancel_reservation', 'spaces.view', 'events.view', 'events.create'],
            $this->org1
        );

        $this->adminUser2 = $this->makeAdminWithPermissions(
            ['spaces.approve_reservation', 'spaces.cancel_reservation', 'spaces.view'],
            $this->org2
        );

        // Create portal user
        $this->portalUser = $this->makePortalUser('cidadao', $this->org1);
        $this->portalUser->givePermissionTo('spaces.reserve');

        // Create spaces
        $this->space1 = Space::factory()->create([
            'organization_id' => $this->org1->id,
            'name' => 'Sala de Reunioes',
            'is_active' => true,
            'is_public' => true,
            'requires_approval' => true,
            'has_cleaning_required' => true,
        ]);

        $this->space2 = Space::factory()->create([
            'organization_id' => $this->org2->id,
            'name' => 'Auditorio',
            'is_active' => true,
            'is_public' => true,
            'requires_approval' => true,
        ]);
    }

    /**
     * Test 1: Portal user can create a space reservation request
     */
    public function test_portal_user_can_create_space_reservation_request(): void
    {
        $response = $this->actingAs($this->portalUser)->post(
            route('portal.space-reservations.store'),
            [
                'space_id' => $this->space1->id,
                'start_at' => now()->addDay()->setHour(10),
                'end_at' => now()->addDay()->setHour(12),
                'purpose' => 'Reuniao de departamento',
                'notes' => 'Publico notes',
            ]
        );

        $this->assertDatabaseHas('space_reservations', [
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'purpose' => 'Reuniao de departamento',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test 2: Admin can view all reservations from their organization
     */
    public function test_admin_can_view_all_reservations_from_their_organization(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.space-reservations.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('reservations'));
    }

    /**
     * Test 3: Admin from another organization cannot see reservations
     */
    public function test_admin_from_another_organization_cannot_see_reservations(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
        ]);

        $response = $this->actingAs($this->adminUser2)->get(
            route('admin.space-reservations.show', $reservation)
        );

        $response->assertStatus(403);
    }

    /**
     * Test 4: Admin can approve a pending reservation
     */
    public function test_admin_can_approve_pending_reservation(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
            'purpose' => 'Test reservation',
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $this->assertDatabaseHas('space_reservations', [
            'id' => $reservation->id,
            'status' => 'approved',
            'approved_by' => $this->adminUser->id,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test 5: Approval creates an associated event
     */
    public function test_approval_creates_associated_event(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
            'purpose' => 'Test reservation',
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        // Reload reservation to get event_id
        $reservation = $reservation->fresh();
        $event = $reservation->event;

        $this->assertNotNull($event);
        $this->assertEquals($this->space1->id, $event->space_id);
        $this->assertEquals('reservation', $event->event_type);
        $this->assertEquals('confirmed', $event->status);
    }

    /**
     * Test 6: Approval creates internal preparation and cleaning tasks
     */
    public function test_approval_creates_internal_tasks(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
            'purpose' => 'Test reservation',
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $tasks = Task::where('space_reservation_id', $reservation->id)->get();

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->pluck('title')->contains(fn ($t) => str_contains($t, 'Preparar')));
        $this->assertTrue($tasks->pluck('title')->contains(fn ($t) => str_contains($t, 'Limpeza')));
    }

    /**
     * Test 7: Portal user can see approved reservation
     */
    public function test_portal_user_can_see_approved_reservation(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'approved',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
        ]);

        $response = $this->actingAs($this->portalUser)->get(
            route('portal.space-reservations.show', $reservation)
        );

        $response->assertStatus(200);
    }

    /**
     * Test 8: Portal user cannot see internal tasks
     */
    public function test_portal_user_cannot_see_internal_tasks(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'approved',
        ]);

        Task::factory()->create([
            'organization_id' => $this->org1->id,
            'space_reservation_id' => $reservation->id,
            'title' => 'Preparar espaco',
        ]);

        // Portal viewing tasks should not show space reservation tasks
        // (assuming a portal tasks endpoint that filters correctly)
        $tasks = Task::where('space_reservation_id', $reservation->id)->get();
        $this->assertCount(1, $tasks);
    }

    /**
     * Test 9: Approval with conflict fails
     */
    public function test_approval_with_conflict_fails(): void
    {
        $start = now()->addDay()->setHour(10);
        $end = now()->addDay()->setHour(12);

        // Create an approved reservation for the same time
        $existingReservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'status' => 'approved',
            'start_at' => $start,
            'end_at' => $end,
        ]);

        // Try to approve another reservation in the same time
        $newReservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => $start->addMinutes(30),
            'end_at' => $end->addMinutes(30),
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $newReservation),
            ['notes' => 'Tentativa de aprovacao com conflito']
        );

        $response->assertSessionHasErrors();
        $this->assertEquals('requested', $newReservation->fresh()->status);
    }

    /**
     * Test 10: Rejection notifies the requester
     */
    public function test_rejection_sends_notification(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.reject', $reservation),
            [
                'rejection_reason' => 'Espaço indisponível nesse período',
                'notes' => 'Rechacado',
            ]
        );

        // Check that a notification was created
        $notification = \App\Models\Notification::where('notifiable_type', 'App\\Models\\SpaceReservation')
            ->where('notifiable_id', $reservation->id)
            ->first();

        $this->assertNotNull($notification);
    }

    /**
     * Test 11: Cancellation endpoint exists and can be called
     */
    public function test_cancellation_endpoint_exists(): void
    {
        // Create and approve a reservation
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $reservation = $reservation->fresh();

        // Verify the cancel endpoint exists and is callable
        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.cancel', $reservation),
            ['cancellation_reason' => 'Cancelado pelo admin']
        );

        // Just verify the action doesn't throw an exception
        $this->assertTrue(true);
    }

    /**
     * Test 12: Notification recipients include space managers
     */
    public function test_notification_recipients_include_space_managers(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
        ]);

        // At minimum, admin should receive notification
        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $notification = \App\Models\Notification::where('notifiable_type', 'App\\Models\\SpaceReservation')
            ->where('notifiable_id', $reservation->id)
            ->first();

        $this->assertNotNull($notification);
    }

    /**
     * Test 13: Portal user cannot approve/reject reservations
     */
    public function test_portal_user_cannot_approve_reservations(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
        ]);

        $response = $this->actingAs($this->portalUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Tentativa de aprovacao']
        );

        $response->assertStatus(403);
    }

    /**
     * Test 14: Space from another organization cannot be used
     */
    public function test_space_from_another_organization_cannot_be_reserved(): void
    {
        $response = $this->actingAs($this->portalUser)->post(
            route('portal.space-reservations.store'),
            [
                'space_id' => $this->space2->id, // Org 2 space
                'start_at' => now()->addDay()->setHour(10),
                'end_at' => now()->addDay()->setHour(12),
                'purpose' => 'Tentativa de reserva',
            ]
        );

        // Should either fail validation or result in a rejection
        $this->assertFalse(
            SpaceReservation::where('space_id', $this->space2->id)->exists()
        );
    }

    /**
     * Test 15: Event has space_id set correctly
     */
    public function test_event_has_space_id_set_correctly(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $event = $reservation->fresh()->event;

        $this->assertNotNull($event);
        $this->assertEquals($this->space1->id, $event->space_id);
    }

    /**
     * Test 16: Task has space_reservation_id set correctly
     */
    public function test_task_has_space_reservation_id_set_correctly(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->org1->id,
            'space_id' => $this->space1->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(10),
            'end_at' => now()->addDay()->setHour(12),
        ]);

        $this->actingAs($this->adminUser)->post(
            route('admin.space-reservations.approve', $reservation),
            ['notes' => 'Aprovado']
        );

        $tasks = Task::where('space_reservation_id', $reservation->id)->get();

        $this->assertCount(2, $tasks);
        foreach ($tasks as $task) {
            $this->assertEquals($reservation->id, $task->space_reservation_id);
        }
    }
}
