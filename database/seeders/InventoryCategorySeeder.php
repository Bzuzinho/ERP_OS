<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if (! $organization) {
            return;
        }

        $categories = [
            'Material de limpeza',
            'Ferramentas',
            'Equipamentos',
            'Viaturas',
            'Mobiliario',
            'Consumiveis administrativos',
            'Material de eventos',
        ];

        foreach ($categories as $name) {
            InventoryCategory::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'organization_id' => $organization->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ],
            );
        }
    }
}
