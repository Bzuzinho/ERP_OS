<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $departments = [
            ['name' => 'Secretaria', 'slug' => 'secretaria', 'description' => 'Departamento de Secretaria'],
            ['name' => 'Manutenção', 'slug' => 'manutencao', 'description' => 'Departamento de Manutenção'],
            ['name' => 'Limpeza Urbana', 'slug' => 'limpeza-urbana', 'description' => 'Departamento de Limpeza Urbana'],
            ['name' => 'Espaços e Equipamentos', 'slug' => 'espacos-equipamentos', 'description' => 'Departamento de Espaços e Equipamentos'],
            ['name' => 'Executivo', 'slug' => 'executivo', 'description' => 'Departamento Executivo'],
        ];

        foreach ($organizations as $organization) {
            foreach ($departments as $dept) {
                Department::firstOrCreate([
                    'organization_id' => $organization->id,
                    'slug' => $dept['slug'],
                ], [
                    'name' => $dept['name'],
                    'description' => $dept['description'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
