<?php

namespace Database\Factories;

use App\Models\DocumentType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentType>
 */
class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Ata', 'Edital', 'Regulamento', 'Formulario', 'Contrato', 'Documento interno',
        ]).'-'.fake()->numberBetween(1, 999);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
