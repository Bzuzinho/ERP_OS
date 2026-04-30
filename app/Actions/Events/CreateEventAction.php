<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CreateEventAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): Event
    {
        return DB::transaction(function () use ($creator, $data) {
            $participants = $data['participants'] ?? [];
            unset($data['participants']);

            $event = Event::create([
                ...$data,
                'organization_id' => $data['organization_id'] ?? $creator->organization_id,
                'created_by' => $creator->id,
            ]);

            foreach ($participants as $participant) {
                $event->participants()->create($participant);
            }

            $this->activityLogger->log(
                subject: $event,
                action: 'event.created',
                user: $creator,
                organization: $event->organization,
                newValues: $event->only(['title', 'event_type', 'status', 'start_at', 'end_at', 'related_ticket_id', 'related_contact_id']),
                description: 'Evento de agenda criado.',
            );

            return $event;
        });
    }
}
