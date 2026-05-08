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

class SpaceReservationScopeTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $orgA;

    private Organization $orgB;

    private User $adminA;

    private User $adminB;

    private User $portalUser;

    private Space $spaceA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->orgA = Organization::factory()->create();
        $this->orgB = Organization::factory()->create();

        $this->adminA = $this->makeAdminWithPermissions([
            'spaces.view',
            'spaces.reserve',
            'spaces.approve_reservation',
            'spaces.cancel_reservation',
        ], $this->orgA);

        $this->adminB = $this->makeAdminWithPermissions([
            'spaces.view',
        ], $this->orgB);

        $this->portalUser = $this->makePortalUser('cidadao', $this->orgA);
        $this->portalUser->givePermissionTo('spaces.reserve');

        $this->spaceA = Space::factory()->create([
            'organization_id' => $this->orgA->id,
            'requires_approval' => true,
            'is_active' => true,
            'is_public' => true,
        ]);
    }

    public function test_other_organization_reservation_is_not_visible(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->orgA->id,
            'space_id' => $this->spaceA->id,
            'requested_by_user_id' => $this->adminA->id,
            'status' => 'requested',
        ]);

        $response = $this->actingAs($this->adminB)->get(route('admin.space-reservations.show', $reservation));
        $response->assertStatus(403);
    }

    public function test_portal_reservation_stays_requested_until_admin_approval(): void
    {
        $this->actingAs($this->portalUser)->post(route('portal.space-reservations.store'), [
            'space_id' => $this->spaceA->id,
            'start_at' => now()->addDays(1)->setHour(10)->setMinute(0),
            'end_at' => now()->addDays(1)->setHour(12)->setMinute(0),
            'purpose' => 'Pedido portal',
        ])->assertRedirect();

        $reservation = SpaceReservation::query()->latest('id')->first();

        $this->assertNotNull($reservation);
        $this->assertSame('requested', $reservation->status);
    }

    public function test_user_without_permission_cannot_approve_or_cancel_reservation(): void
    {
        $limitedUser = $this->makePortalUser('cidadao', $this->orgA);

        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->orgA->id,
            'space_id' => $this->spaceA->id,
            'requested_by_user_id' => $this->portalUser->id,
            'status' => 'requested',
            'start_at' => now()->addDays(2)->setHour(10)->setMinute(0),
            'end_at' => now()->addDays(2)->setHour(12)->setMinute(0),
        ]);

        $this->actingAs($limitedUser)->post(route('admin.space-reservations.approve', $reservation))->assertStatus(403);
        $this->actingAs($limitedUser)->post(route('admin.space-reservations.cancel', $reservation), ['cancellation_reason' => 'x'])->assertStatus(403);
    }
}
