<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentAccessRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentAccessRule>
 */
class DocumentAccessRuleFactory extends Factory
{
    protected $model = DocumentAccessRule::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'contact_id' => null,
            'role_name' => null,
            'permission' => fake()->randomElement(['view', 'download', 'manage']),
            'expires_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function forContact(Contact $contact): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'contact_id' => $contact->id,
        ]);
    }
}
