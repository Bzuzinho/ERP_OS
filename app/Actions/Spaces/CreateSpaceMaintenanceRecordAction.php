<?php

namespace App\Actions\Spaces;

use App\Models\SpaceMaintenanceRecord;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CreateSpaceMaintenanceRecordAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): SpaceMaintenanceRecord
    {
        $record = SpaceMaintenanceRecord::create([
            ...$data,
            'organization_id' => $data['organization_id'] ?? $creator->organization_id,
        ]);

        $this->activityLogger->log(
            subject: $record,
            action: 'space.maintenance.created',
            user: $creator,
            organization: $record->organization,
            newValues: $record->only(['space_id', 'type', 'status', 'title', 'assigned_to']),
            description: 'Registo de manutencao criado.',
        );

        return $record;
    }
}
