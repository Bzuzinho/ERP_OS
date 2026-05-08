<?php

namespace Tests\Feature\SpaceReservations;

use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SpaceReservationAgendaSyncTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $organization;

    private User $approver;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->organization = Organization::factory()->create();
        $this->approver = $this->makeAdminWithPermissions([
            'spaces.view',
            'spaces.approve_reservation',
            'spaces.cancel_reservation',
            'events.view',
            'events.create',
        ], $this->organization);

        $this->space = Space::factory()->create([
            'organization_id' => $this->organization->id,
            'requires_approval' => true,
            'is_active' => true,
        ]);
    }

    public function test_approved_reservation_creates_or_syncs_event(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'requested',
            'start_at' => now()->addDays(2)->setHour(9)->setMinute(0),
            'end_at' => now()->addDays(2)->setHour(11)->setMinute(0),
            'purpose' => 'Sessao comunitaria',
        ]);

        $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $reservation));

        $reservation->refresh();

        $this->assertNotNull($reservation->event_id);
        $this->assertEquals($this->space->id, $reservation->event?->space_id);
        $this->assertEquals('reservation', $reservation->event?->event_type);
        $this->assertEquals('confirmed', $reservation->event?->status);
        $this->assertEquals($reservation->start_at?->toDateTimeString(), $reservation->event?->start_at?->toDateTimeString());
        $this->assertEquals($reservation->end_at?->toDateTimeString(), $reservation->event?->end_at?->toDateTimeString());
    }

    public function test_cancelled_reservation_cancels_associated_event(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'requested',
            'start_at' => now()->addDays(3)->setHour(14)->setMinute(0),
            'end_at' => now()->addDays(3)->setHour(16)->setMinute(0),
        ]);

        $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $reservation));
        $this->actingAs($this->approver)->post(route('admin.space-reservations.cancel', $reservation), [
            'cancellation_reason' => 'Alteracao de agenda',
        ]);

        $reservation->refresh();

        $this->assertEquals('cancelled', $reservation->status);
        $this->assertEquals('cancelled', $reservation->event?->status);
    }

    public function test_approval_blocks_conflicts_for_same_space_and_time(): void
    {
        $startAt = now()->addDays(4)->setHour(10)->setMinute(0);
        $endAt = now()->addDays(4)->setHour(12)->setMinute(0);

        SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'approved',
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        $conflicting = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'requested',
            'start_at' => (clone $startAt)->addMinutes(30),
            'end_at' => (clone $endAt)->addMinutes(30),
        ]);

        $response = $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $conflicting));

        $response->assertSessionHasErrors();
        $this->assertEquals('requested', $conflicting->fresh()->status);
    }
}
