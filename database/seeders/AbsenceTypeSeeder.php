<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class AbsenceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = Organization::all();

        $types = [
            ['name' => 'Férias', 'slug' => 'ferias', 'description' => 'Férias anuais', 'requires_approval' => false, 'is_paid' => true],
            ['name' => 'Baixa Médica', 'slug' => 'baixa-medica', 'description' => 'Baixa médica justificada', 'requires_approval' => true, 'is_paid' => true],
            ['name' => 'Falta Justificada', 'slug' => 'falta-justificada', 'description' => 'Ausência justificada', 'requires_approval' => true, 'is_paid' => true],
            ['name' => 'Falta Injustificada', 'slug' => 'falta-injustificada', 'description' => 'Ausência sem justificação', 'requires_approval' => true, 'is_paid' => false],
            ['name' => 'Apoio à Família', 'slug' => 'apoio-familia', 'description' => 'Licença por apoio à família', 'requires_approval' => true, 'is_paid' => true],
            ['name' => 'Formação', 'slug' => 'formacao', 'description' => 'Ausência para formação', 'requires_approval' => true, 'is_paid' => true],
        ];

        foreach ($organizations as $organization) {
            foreach ($types as $type) {
                AbsenceType::firstOrCreate([
                    'organization_id' => $organization->id,
                    'slug' => $type['slug'],
                ], [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'requires_approval' => $type['requires_approval'],
                    'is_paid' => $type['is_paid'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
