<?php

namespace Tests\Feature\ResourceRequests;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Organization;
use App\Models\ResourceRequest;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class ResourceRequestScopeTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    public function test_user_from_other_organization_cannot_create_request(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $userA = $this->makeAdminWithPermissions(['admin.access', 'resources.create'], $orgA);

        $categoryB = InventoryCategory::factory()->create(['organization_id' => $orgB->id]);
        $locationB = InventoryLocation::factory()->create(['organization_id' => $orgB->id]);

        $itemB = InventoryItem::factory()->create([
            'organization_id' => $orgB->id,
            'inventory_category_id' => $categoryB->id,
            'inventory_location_id' => $locationB->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->actingAs($userA)
            ->post(route('admin.resource-requests.store'), [
                'organization_id' => $orgA->id,
                'title' => 'Req invalida',
                'items' => [
                    ['inventory_item_id' => $itemB->id, 'quantity_requested' => 1],
                ],
            ])
            ->assertSessionHasErrors('items.0.inventory_item_id');
    }

    public function test_user_without_permission_cannot_approve(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $org = Organization::factory()->create();

        $creator = $this->makeAdminWithPermissions(['admin.access', 'resources.create'], $org);
        $approverWithoutPermission = User::factory()->create(['organization_id' => $org->id]);
        $approverWithoutPermission->givePermissionTo('admin.access');

        $category = InventoryCategory::factory()->create(['organization_id' => $org->id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $org->id]);

        $item = InventoryItem::factory()->create([
            'organization_id' => $org->id,
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $request = ResourceRequest::query()->create([
            'organization_id' => $org->id,
            'requested_by' => $creator->id,
            'status' => 'requested',
            'title' => 'Req',
        ]);

        $requestItem = $request->items()->create([
            'inventory_item_id' => $item->id,
            'quantity_requested' => 2,
        ]);

        $this->actingAs($approverWithoutPermission)
            ->post(route('admin.resource-requests.approve', $request), [
                'items' => [
                    $requestItem->id => ['quantity_approved' => 1],
                ],
            ])
            ->assertForbidden();
    }

    public function test_inactive_item_cannot_be_requested(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $org = Organization::factory()->create();
        $user = $this->makeAdminWithPermissions(['admin.access', 'resources.create'], $org);

        $category = InventoryCategory::factory()->create(['organization_id' => $org->id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $org->id]);

        $inactiveItem = InventoryItem::factory()->create([
            'organization_id' => $org->id,
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'is_active' => false,
            'status' => 'inactive',
        ]);

        $this->actingAs($user)
            ->post(route('admin.resource-requests.store'), [
                'organization_id' => $org->id,
                'title' => 'Req invalida',
                'items' => [
                    ['inventory_item_id' => $inactiveItem->id, 'quantity_requested' => 1],
                ],
            ])
            ->assertSessionHasErrors('items.0.inventory_item_id');
    }
}
