<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();
        if (! $organization) {
            return;
        }

        $areaNames = [
            'Atendimento',
            'Secretaria',
            'Manutencao',
            'Higiene Urbana',
            'Espacos',
            'Armazem',
            'Recursos Humanos',
            'Executivo',
            'Documentacao',
            'Eventos',
            'Iluminacao Publica',
            'Acao Social',
        ];

        $areas = collect($areaNames)->mapWithKeys(function (string $name) use ($organization) {
            $area = ServiceArea::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'description' => 'Area funcional da organizacao.',
                    'is_active' => true,
                ]
            );

            return [$name => $area];
        });

        $mapping = [
            'Filipa Costa' => ['Atendimento', 'Secretaria'],
            'Rui Santos' => ['Manutencao', 'Higiene Urbana'],
            'Joao Pereira' => ['Manutencao'],
            'Ana Ferreira' => ['Armazem'],
            'Marta Lima' => ['Recursos Humanos'],
            'Carla Marques' => ['Executivo'],
        ];

        foreach ($mapping as $userName => $areaList) {
            $user = User::query()
                ->where('organization_id', $organization->id)
                ->where('name', $userName)
                ->first();

            if (! $user) {
                continue;
            }

            foreach ($areaList as $areaName) {
                $area = $areas->get($areaName);
                if (! $area) {
                    continue;
                }

                $area->users()->syncWithoutDetaching([
                    $user->id => ['role' => null, 'is_primary' => false],
                ]);
            }
        }

        $admin = User::query()
            ->where('organization_id', $organization->id)
            ->where('email', 'admin@juntaos.local')
            ->first();

        if ($admin) {
            $areas
                ->filter(fn (ServiceArea $area) => in_array($area->slug, ['atendimento', 'executivo'], true))
                ->each(function (ServiceArea $area) use ($admin): void {
                    $area->users()->syncWithoutDetaching([
                        $admin->id => ['role' => null, 'is_primary' => false],
                    ]);
                });
        }
    }
}
