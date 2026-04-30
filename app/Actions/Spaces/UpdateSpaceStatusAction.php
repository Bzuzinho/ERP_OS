<?php

namespace App\Actions\Spaces;

use App\Models\Space;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateSpaceStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(Space $space, string $status, User $performedBy): Space
    {
        $oldStatus = $space->status;
        if ($oldStatus === $status) {
            return $space;
        }

        $space->status = $status;
        $space->save();

        $this->activityLogger->log(
            subject: $space,
            action: 'space.status_updated',
            user: $performedBy,
            organization: $space->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado do espaco atualizado.',
        );

        return $space;
    }
}
