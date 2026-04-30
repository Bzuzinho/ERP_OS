<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if (! $organization) {
            return;
        }

        $categories = InventoryCategory::query()->where('organization_id', $organization->id)->get()->keyBy('name');
        $locations = InventoryLocation::query()->where('organization_id', $organization->id)->get()->keyBy('name');

        $items = [
            ['name' => 'Sacos do lixo', 'sku' => 'INV-SACO-001', 'category' => 'Material de limpeza', 'location' => 'Armazem principal', 'item_type' => 'consumable', 'unit' => 'box', 'current_stock' => 25, 'minimum_stock' => 10],
            ['name' => 'Detergente multiusos', 'sku' => 'INV-DETER-001', 'category' => 'Material de limpeza', 'location' => 'Armazem principal', 'item_type' => 'consumable', 'unit' => 'liter', 'current_stock' => 18, 'minimum_stock' => 8],
            ['name' => 'Berbequim', 'sku' => 'INV-BERB-001', 'category' => 'Ferramentas', 'location' => 'Edificio da Junta', 'item_type' => 'tool', 'unit' => 'unit', 'current_stock' => 3, 'minimum_stock' => 1, 'is_loanable' => true],
            ['name' => 'Mesa dobravel', 'sku' => 'INV-MESA-001', 'category' => 'Mobiliario', 'location' => 'Centro Comunitario', 'item_type' => 'furniture', 'unit' => 'unit', 'current_stock' => 12, 'minimum_stock' => 4, 'is_loanable' => true],
            ['name' => 'Cadeiras empilhaveis', 'sku' => 'INV-CADEI-001', 'category' => 'Mobiliario', 'location' => 'Centro Comunitario', 'item_type' => 'furniture', 'unit' => 'unit', 'current_stock' => 80, 'minimum_stock' => 30, 'is_loanable' => true],
            ['name' => 'Extensao eletrica', 'sku' => 'INV-EXT-001', 'category' => 'Equipamentos', 'location' => 'Armazem principal', 'item_type' => 'equipment', 'unit' => 'unit', 'current_stock' => 10, 'minimum_stock' => 3, 'is_loanable' => true],
        ];

        foreach ($items as $item) {
            InventoryItem::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($item['name']),
                ],
                [
                    'organization_id' => $organization->id,
                    'inventory_category_id' => $categories[$item['category']]?->id,
                    'inventory_location_id' => $locations[$item['location']]?->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'sku' => $item['sku'],
                    'item_type' => $item['item_type'],
                    'unit' => $item['unit'],
                    'current_stock' => $item['current_stock'],
                    'minimum_stock' => $item['minimum_stock'],
                    'status' => 'active',
                    'is_stock_tracked' => true,
                    'is_loanable' => $item['is_loanable'] ?? false,
                    'is_active' => true,
                ],
            );
        }
    }
}
