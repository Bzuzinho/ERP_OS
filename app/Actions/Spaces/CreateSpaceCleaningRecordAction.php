<?php

namespace App\Actions\Spaces;

use App\Models\SpaceCleaningRecord;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CreateSpaceCleaningRecordAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): SpaceCleaningRecord
    {
        $record = SpaceCleaningRecord::create([
            ...$data,
            'organization_id' => $data['organization_id'] ?? $creator->organization_id,
        ]);

        $this->activityLogger->log(
            subject: $record,
            action: 'space.cleaning.created',
            user: $creator,
            organization: $record->organization,
            newValues: $record->only(['space_id', 'space_reservation_id', 'status', 'scheduled_at', 'assigned_to']),
            description: 'Registo de limpeza criado.',
        );

        return $record;
    }
}
