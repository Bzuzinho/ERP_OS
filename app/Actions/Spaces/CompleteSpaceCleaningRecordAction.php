<?php

namespace App\Actions\Spaces;

use App\Models\SpaceCleaningRecord;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CompleteSpaceCleaningRecordAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(SpaceCleaningRecord $record, User $performedBy): SpaceCleaningRecord
    {
        $oldStatus = $record->status;
        $record->status = 'completed';
        $record->completed_at = now();
        $record->completed_by = $performedBy->id;
        $record->save();

        $this->activityLogger->log(
            subject: $record,
            action: 'space.cleaning.completed',
            user: $performedBy,
            organization: $record->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'completed', 'completed_by' => $performedBy->id],
            description: 'Limpeza concluida.',
        );

        return $record;
    }
}
