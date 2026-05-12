<?php

namespace Tests\Feature\ResourceRequests;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Organization;
use App\Models\ResourceRequest;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class ResourceRequestFlowTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $organization;
    private User $admin;
    private InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->organization = Organization::factory()->create();
        $this->admin = $this->makeAdminWithPermissions([
            'admin.access',
            'resources.view',
            'resources.create',
            'resources.approve',
            'resources.deliver',
            'resources.return',
            'resources.manage',
            'spaces.view',
            'spaces.reserve',
            'tasks.view',
        ], $this->organization);

        $category = InventoryCategory::factory()->create(['organization_id' => $this->organization->id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $this->organization->id]);

        $this->item = InventoryItem::factory()->create([
            'organization_id' => $this->organization->id,
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'item_type' => 'equipment',
            'is_loanable' => true,
            'is_stock_tracked' => true,
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function test_creates_request_associated_with_reservation(): void
    {
        $space = Space::factory()->create(['organization_id' => $this->organization->id]);
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $space->id,
            'requested_by_user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.space-reservations.resource-requests.store', $reservation), [
                'organization_id' => $this->organization->id,
                'title' => 'Materiais para reserva',
                'items' => [
                    ['inventory_item_id' => $this->item->id, 'quantity_requested' => 2],
                ],
            ])
            ->assertRedirect();

        $request = ResourceRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertSame(SpaceReservation::class, $request->requestable_type);
        $this->assertSame($reservation->id, $request->requestable_id);
    }

    public function test_creates_request_associated_with_task(): void
    {
        $task = Task::factory()->create([
            'organization_id' => $this->organization->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.tasks.resource-requests.store', $task), [
                'organization_id' => $this->organization->id,
                'title' => 'Materiais para tarefa',
                'items' => [
                    ['inventory_item_id' => $this->item->id, 'quantity_requested' => 1],
                ],
            ])
            ->assertRedirect();

        $request = ResourceRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertSame(Task::class, $request->requestable_type);
        $this->assertSame($task->id, $request->requestable_id);
    }

    public function test_approval_and_rejection_fields_are_persisted(): void
    {
        $request = ResourceRequest::query()->create([
            'organization_id' => $this->organization->id,
            'requested_by' => $this->admin->id,
            'status' => 'requested',
            'title' => 'Req',
        ]);

        $request->items()->create([
            'inventory_item_id' => $this->item->id,
            'quantity_requested' => 5,
        ]);

        $item = $request->items()->first();

        $this->actingAs($this->admin)
            ->post(route('admin.resource-requests.approve', $request), [
                'items' => [
                    $item->id => ['quantity_approved' => 4],
                ],
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertSame($this->admin->id, $request->approved_by);
        $this->assertNotNull($request->approved_at);

        $request->update(['status' => 'requested', 'approved_by' => null, 'approved_at' => null]);

        $this->actingAs($this->admin)
            ->post(route('admin.resource-requests.reject', $request), [
                'rejection_reason' => 'Sem disponibilidade',
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('rejected', $request->status);
        $this->assertSame('Sem disponibilidade', $request->rejection_reason);
    }

    public function test_delivery_cannot_exceed_approved_and_return_cannot_exceed_delivered(): void
    {
        $request = ResourceRequest::query()->create([
            'organization_id' => $this->organization->id,
            'requested_by' => $this->admin->id,
            'status' => 'prepared',
            'title' => 'Req entrega',
        ]);

        $item = $request->items()->create([
            'inventory_item_id' => $this->item->id,
            'quantity_requested' => 5,
            'quantity_approved' => 4,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.resource-requests.deliver', $request), [
                'items' => [
                    $item->id => ['quantity_delivered' => 5],
                ],
            ])
            ->assertSessionHasErrors();

        $request->update(['status' => 'delivered']);
        $item->update(['quantity_delivered' => 3, 'quantity_returned' => 0]);

        $this->actingAs($this->admin)
            ->post(route('admin.resource-requests.return', $request), [
                'items' => [
                    $item->id => ['quantity_returned' => 4],
                ],
            ])
            ->assertSessionHasErrors();
    }
}
