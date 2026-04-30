<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Ata',
            'Edital',
            'Regulamento',
            'Formulario',
            'Contrato',
            'Documento interno',
            'Documento de pedido',
            'Documento de reuniao',
            'Documento de associacao',
        ];

        foreach ($types as $name) {
            DocumentType::query()->updateOrCreate(
                [
                    'organization_id' => null,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'is_active' => true,
                ],
            );
        }
    }
}
