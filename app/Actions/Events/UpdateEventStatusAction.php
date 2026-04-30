<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateEventStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(Event $event, string $status, User $performedBy): Event
    {
        $oldStatus = $event->status;

        $event->status = $status;
        $event->save();

        $this->activityLogger->log(
            subject: $event,
            action: 'event.status_updated',
            user: $performedBy,
            organization: $event->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado do evento atualizado.',
        );

        return $event;
    }
}
