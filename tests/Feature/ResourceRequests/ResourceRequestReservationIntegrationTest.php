<?php

namespace Tests\Feature\ResourceRequests;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Organization;
use App\Models\ResourceRequest;
use App\Models\Space;
use App\Models\SpaceReservation;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class ResourceRequestReservationIntegrationTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    public function test_space_reservation_show_displays_associated_resource_requests_data(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $org = Organization::factory()->create();
        $user = $this->makeAdminWithPermissions([
            'admin.access', 'spaces.view', 'resources.view', 'resources.create',
        ], $org);

        $space = Space::factory()->create(['organization_id' => $org->id]);
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $org->id,
            'space_id' => $space->id,
            'requested_by_user_id' => $user->id,
        ]);

        $resourceRequest = ResourceRequest::query()->create([
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => 'requested',
            'requestable_type' => SpaceReservation::class,
            'requestable_id' => $reservation->id,
            'title' => 'Req para reserva',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.space-reservations.show', $reservation));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('reservation.id', $reservation->id)
            ->where('reservation.resource_requests.0.id', $resourceRequest->id)
            ->where('reservation.resource_requests.0.title', 'Req para reserva')
        );
    }
}
