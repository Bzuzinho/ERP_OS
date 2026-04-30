<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Event;
use App\Models\MeetingMinute;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingMinute>
 */
class MeetingMinuteFactory extends Factory
{
    protected $model = MeetingMinute::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'event_id' => Event::factory(),
            'document_id' => null,
            'title' => 'Ata - '.fake()->sentence(3),
            'summary' => fake()->optional()->paragraph(),
            'status' => 'draft',
            'approved_at' => null,
            'approved_by' => null,
            'created_by' => User::factory(),
        ];
    }

    public function approved(User $approver): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    public function withDocument(Document $document): static
    {
        return $this->state(fn () => [
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
        ]);
    }
}
