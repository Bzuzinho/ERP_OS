<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();

        if (! $organization) {
            return;
        }

        $spaces = [
            ['name' => 'Sala de Reunioes', 'capacity' => 12, 'is_public' => false, 'requires_approval' => true],
            ['name' => 'Auditorio da Junta', 'capacity' => 120, 'is_public' => true, 'requires_approval' => true],
            ['name' => 'Sala Polivalente', 'capacity' => 40, 'is_public' => true, 'requires_approval' => true],
            ['name' => 'Centro Comunitario', 'capacity' => 80, 'is_public' => true, 'requires_approval' => true],
            ['name' => 'Campo de Jogos', 'capacity' => 200, 'is_public' => true, 'requires_approval' => false],
        ];

        foreach ($spaces as $space) {
            Space::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($space['name']),
                ],
                [
                    'organization_id' => $organization->id,
                    'name' => $space['name'],
                    'slug' => Str::slug($space['name']),
                    'description' => 'Espaco municipal disponivel para atividades da junta.',
                    'location_text' => 'Edificio da Junta',
                    'capacity' => $space['capacity'],
                    'status' => 'available',
                    'requires_approval' => $space['requires_approval'],
                    'has_cleaning_required' => true,
                    'has_deposit' => false,
                    'deposit_amount' => null,
                    'price' => null,
                    'rules' => 'Respeitar horarios e regulamento municipal.',
                    'is_public' => $space['is_public'],
                    'is_active' => true,
                ],
            );
        }
    }
}
