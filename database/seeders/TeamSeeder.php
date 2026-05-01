<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $teams = [
            ['name' => 'Equipa de Manutenção', 'department_slug' => 'manutencao', 'description' => 'Equipa responsável por manutenção'],
            ['name' => 'Equipa de Limpeza', 'department_slug' => 'limpeza-urbana', 'description' => 'Equipa responsável por limpeza urbana'],
            ['name' => 'Atendimento', 'department_slug' => 'secretaria', 'description' => 'Equipa de atendimento'],
        ];

        foreach ($organizations as $organization) {
            foreach ($teams as $teamData) {
                $department = Department::where('organization_id', $organization->id)
                    ->where('slug', $teamData['department_slug'])
                    ->first();

                if ($department) {
                    Team::firstOrCreate([
                        'organization_id' => $organization->id,
                        'slug' => Str::slug($teamData['name']),
                    ], [
                        'name' => $teamData['name'],
                        'department_id' => $department->id,
                        'description' => $teamData['description'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
