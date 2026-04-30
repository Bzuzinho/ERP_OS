<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'document_type_id' => DocumentType::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'file_path' => 'documents/test/'.fake()->uuid().'.pdf',
            'file_name' => fake()->uuid().'.pdf',
            'original_name' => 'documento.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_by' => User::factory(),
            'visibility' => 'internal',
            'related_type' => null,
            'related_id' => null,
            'current_version' => 1,
            'status' => 'active',
            'is_active' => true,
        ];
    }
}
