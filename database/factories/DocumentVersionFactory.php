<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    protected $model = DocumentVersion::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'version' => 1,
            'file_path' => 'documents/test/'.fake()->uuid().'.pdf',
            'file_name' => fake()->uuid().'.pdf',
            'original_name' => 'versao.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
