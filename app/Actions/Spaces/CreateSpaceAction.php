<?php

namespace App\Actions\Spaces;

use App\Models\Space;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSpaceAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): Space
    {
        return DB::transaction(function () use ($creator, $data) {
            $organizationId = $data['organization_id'] ?? $creator->organization_id;
            $baseSlug = Str::slug($data['slug'] ?? $data['name']);
            $slug = $this->makeUniqueSlug($organizationId, $baseSlug);

            $space = Space::create([
                ...$data,
                'organization_id' => $organizationId,
                'slug' => $slug,
            ]);

            $this->activityLogger->log(
                subject: $space,
                action: 'space.created',
                user: $creator,
                organization: $space->organization,
                newValues: $space->only(['name', 'slug', 'status', 'is_public', 'is_active']),
                description: 'Espaco criado.',
            );

            return $space;
        });
    }

    private function makeUniqueSlug(?int $organizationId, string $baseSlug): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'espaco';
        $counter = 1;

        while (Space::query()->where('organization_id', $organizationId)->where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        return $slug;
    }
}
