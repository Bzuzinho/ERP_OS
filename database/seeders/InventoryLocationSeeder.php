<?php

namespace Database\Seeders;

use App\Models\InventoryLocation;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryLocationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if (! $organization) {
            return;
        }

        $locations = [
            'Armazem principal',
            'Edificio da Junta',
            'Centro Comunitario',
            'Viaturas',
        ];

        foreach ($locations as $name) {
            InventoryLocation::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'organization_id' => $organization->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => 'Localizacao de inventario da junta.',
                    'is_active' => true,
                ],
            );
        }
    }
}
