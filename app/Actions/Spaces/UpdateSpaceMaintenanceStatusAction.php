<?php

namespace App\Actions\Spaces;

use App\Models\SpaceMaintenanceRecord;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateSpaceMaintenanceStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(SpaceMaintenanceRecord $record, string $status, User $performedBy): SpaceMaintenanceRecord
    {
        $oldStatus = $record->status;
        $record->status = $status;

        if ($status === 'completed') {
            $record->completed_at = now();
            $record->completed_by = $performedBy->id;
        }

        $record->save();

        $this->activityLogger->log(
            subject: $record,
            action: 'space.maintenance.status_updated',
            user: $performedBy,
            organization: $record->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado da manutencao atualizado.',
        );

        return $record;
    }
}
