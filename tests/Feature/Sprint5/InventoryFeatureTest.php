<?php

namespace Tests\Feature\Sprint5;

use App\Models\InventoryBreakage;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Services\Inventory\InventoryLoanService;
use App\Services\Inventory\InventoryStockService;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class InventoryFeatureTest extends TestCase
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

    public function test_super_admin_consegue_listar_inventory_items(): void
    {
        $admin = $this->makeSuperAdmin();
        InventoryItem::factory()->create(['organization_id' => $admin->organization_id]);

        $this->actingAs($admin)
            ->get(route('admin.inventory-items.index'))
            ->assertOk();
    }

    public function test_utilizador_sem_inventory_view_nao_consegue_listar_inventory_items(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.inventory-items.index'))
            ->assertForbidden();
    }

    public function test_utilizador_com_inventory_create_consegue_criar_item(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.create', 'inventory.view', 'inventory.move']);
        $category = InventoryCategory::factory()->create(['organization_id' => $admin->organization_id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $admin->organization_id]);

        $this->actingAs($admin)->post(route('admin.inventory-items.store'), [
            'name' => 'Fita adesiva',
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'item_type' => 'consumable',
            'unit' => 'unit',
            'current_stock' => 12,
            'minimum_stock' => 4,
            'status' => 'active',
            'is_stock_tracked' => true,
            'is_loanable' => false,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'organization_id' => $admin->organization_id,
            'name' => 'Fita adesiva',
        ]);
    }

    public function test_criar_item_com_stock_inicial_cria_movimento_inicial(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.create', 'inventory.view', 'inventory.move']);

        $this->actingAs($admin)->post(route('admin.inventory-items.store'), [
            'name' => 'Luvas',
            'item_type' => 'consumable',
            'unit' => 'box',
            'current_stock' => 5,
            'status' => 'active',
            'is_stock_tracked' => true,
            'is_loanable' => false,
            'is_active' => true,
        ])->assertRedirect();

        $item = InventoryItem::query()->where('name', 'Luvas')->firstOrFail();

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'movement_type' => 'entry',
            'quantity' => 5,
        ]);

        $this->assertSame(5.0, (float) $item->fresh()->current_stock);
    }

    public function test_movimento_entry_aumenta_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.move']);
        $item = InventoryItem::factory()->create(['organization_id' => $admin->organization_id, 'current_stock' => 2, 'is_stock_tracked' => true]);

        $this->actingAs($admin)->post(route('admin.inventory-movements.store'), [
            'inventory_item_id' => $item->id,
            'movement_type' => 'entry',
            'quantity' => 3,
        ])->assertRedirect();

        $this->assertSame(5.0, (float) $item->fresh()->current_stock);
    }

    public function test_movimento_exit_diminui_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.move']);
        $item = InventoryItem::factory()->create(['organization_id' => $admin->organization_id, 'current_stock' => 9, 'is_stock_tracked' => true]);

        $this->actingAs($admin)->post(route('admin.inventory-movements.store'), [
            'inventory_item_id' => $item->id,
            'movement_type' => 'exit',
            'quantity' => 4,
        ])->assertRedirect();

        $this->assertSame(5.0, (float) $item->fresh()->current_stock);
    }

    public function test_nao_permite_saida_acima_do_stock_disponivel(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.move']);
        $item = InventoryItem::factory()->create(['organization_id' => $admin->organization_id, 'current_stock' => 1, 'is_stock_tracked' => true]);

        $this->actingAs($admin)->from(route('admin.inventory-movements.create'))->post(route('admin.inventory-movements.store'), [
            'inventory_item_id' => $item->id,
            'movement_type' => 'exit',
            'quantity' => 3,
        ])->assertSessionHasErrors();

        $this->assertSame(1.0, (float) $item->fresh()->current_stock);
    }

    public function test_item_com_stock_abaixo_do_minimo_e_identificado_como_low_stock(): void
    {
        $item = InventoryItem::factory()->create([
            'current_stock' => 2,
            'minimum_stock' => 5,
            'is_stock_tracked' => true,
        ]);

        $status = app(InventoryStockService::class)->getStockStatus($item);

        $this->assertSame('low', $status);
    }

    public function test_criar_loan_diminui_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.loan', 'inventory.move']);
        $item = InventoryItem::factory()->create([
            'organization_id' => $admin->organization_id,
            'current_stock' => 7,
            'is_stock_tracked' => true,
            'is_loanable' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.inventory-loans.store'), [
            'inventory_item_id' => $item->id,
            'borrower_user_id' => $admin->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->assertSame(5.0, (float) $item->fresh()->current_stock);
    }

    public function test_devolver_loan_aumenta_stock_e_muda_status_para_returned(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.loan', 'inventory.return', 'inventory.move']);
        $item = InventoryItem::factory()->create([
            'organization_id' => $admin->organization_id,
            'current_stock' => 10,
            'is_stock_tracked' => true,
            'is_loanable' => true,
        ]);

        $loan = InventoryLoan::factory()->create([
            'organization_id' => $admin->organization_id,
            'inventory_item_id' => $item->id,
            'borrower_user_id' => $admin->id,
            'quantity' => 3,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.inventory-loans.return', $loan), [
            'return_notes' => 'Devolvido em boas condicoes',
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_loans', [
            'id' => $loan->id,
            'status' => 'returned',
        ]);

        $this->assertSame(13.0, (float) $item->fresh()->current_stock);
    }

    public function test_loan_overdue_e_identificado_se_expected_return_at_passou(): void
    {
        $loan = InventoryLoan::factory()->create([
            'status' => 'active',
            'loaned_at' => now()->subDays(5),
            'expected_return_at' => now()->subDay(),
        ]);

        $found = app(InventoryLoanService::class)
            ->overdueLoansQuery()
            ->whereKey($loan->id)
            ->exists();

        $this->assertTrue($found);
    }

    public function test_reportar_breakage_diminui_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.breakage', 'inventory.move']);
        $item = InventoryItem::factory()->create(['organization_id' => $admin->organization_id, 'current_stock' => 6, 'is_stock_tracked' => true]);

        $this->actingAs($admin)->post(route('admin.inventory-breakages.store'), [
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'breakage_type' => 'damaged',
        ])->assertRedirect();

        $this->assertSame(4.0, (float) $item->fresh()->current_stock);
        $this->assertDatabaseHas('inventory_breakages', ['inventory_item_id' => $item->id]);
    }

    public function test_aprovar_restock_request_muda_status_para_approved(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.restock', 'inventory.approve_restock']);
        $request = InventoryRestockRequest::factory()->create([
            'organization_id' => $admin->organization_id,
            'status' => 'requested',
            'requested_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.inventory-restock-requests.approve', $request), [
            'quantity_approved' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_restock_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'quantity_approved' => 10,
        ]);
    }

    public function test_completar_restock_request_cria_movimento_restock_e_aumenta_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['inventory.view', 'inventory.restock', 'inventory.move']);
        $item = InventoryItem::factory()->create(['organization_id' => $admin->organization_id, 'current_stock' => 2]);

        $request = InventoryRestockRequest::factory()->create([
            'organization_id' => $admin->organization_id,
            'inventory_item_id' => $item->id,
            'status' => 'approved',
            'quantity_requested' => 7,
            'quantity_approved' => 5,
            'requested_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.inventory-restock-requests.complete', $request), [
            'notes' => 'Rececao em armazem',
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_restock_requests', [
            'id' => $request->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'movement_type' => 'restock',
            'quantity' => 5,
        ]);

        $this->assertSame(7.0, (float) $item->fresh()->current_stock);
    }

    public function test_policies_bloqueiam_acesso_sem_permissoes(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $item = InventoryItem::factory()->create(['organization_id' => $user->organization_id]);

        $this->actingAs($user)->get(route('admin.inventory-items.show', $item))->assertForbidden();
        $this->actingAs($user)->get(route('admin.inventory-loans.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.inventory-movements.index'))->assertForbidden();
    }
}
