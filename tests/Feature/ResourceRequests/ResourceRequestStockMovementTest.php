<?php

namespace Tests\Feature\ResourceRequests;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\Organization;
use App\Models\ResourceRequest;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class ResourceRequestStockMovementTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    public function test_consumable_stock_tracked_reduces_on_delivery_and_restores_on_return(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $org = Organization::factory()->create();

        $user = $this->makeAdminWithPermissions([
            'admin.access', 'resources.deliver', 'resources.return', 'resources.manage',
        ], $org);

        $category = InventoryCategory::factory()->create(['organization_id' => $org->id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $org->id]);

        $item = InventoryItem::factory()->create([
            'organization_id' => $org->id,
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'item_type' => 'consumable',
            'current_stock' => 10,
            'is_stock_tracked' => true,
            'is_loanable' => false,
            'is_active' => true,
            'status' => 'active',
        ]);

        $request = ResourceRequest::query()->create([
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => 'prepared',
            'title' => 'Req stock',
        ]);

        $requestItem = $request->items()->create([
            'inventory_item_id' => $item->id,
            'quantity_requested' => 3,
            'quantity_approved' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('admin.resource-requests.deliver', $request), [
                'items' => [
                    $requestItem->id => ['quantity_delivered' => 3],
                ],
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame('7.00', $item->current_stock);

        $this->assertDatabaseHas('inventory_movements', [
            'resource_request_id' => $request->id,
            'resource_request_item_id' => $requestItem->id,
            'movement_type' => 'consumption',
        ]);

        $request->refresh();
        $this->assertSame('delivered', $request->status);

        $this->actingAs($user)
            ->post(route('admin.resource-requests.return', $request), [
                'items' => [
                    $requestItem->id => ['quantity_returned' => 2],
                ],
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame('9.00', $item->current_stock);

        $this->assertDatabaseHas('inventory_movements', [
            'resource_request_id' => $request->id,
            'resource_request_item_id' => $requestItem->id,
            'movement_type' => 'return',
        ]);
    }
}
