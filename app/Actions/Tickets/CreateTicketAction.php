<?php

namespace App\Actions\Tickets;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use App\Services\Tickets\TicketReferenceGenerator;
use Illuminate\Support\Facades\DB;

class CreateTicketAction
{
    public function __construct(
        private readonly TicketReferenceGenerator $referenceGenerator,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $creator, array $data): Ticket
    {
        return DB::transaction(function () use ($creator, $data) {
            $organizationId = isset($data['organization_id']) ? (int) $data['organization_id'] : (int) $creator->organization_id;
            $organization = Organization::query()->find($organizationId) ?? $creator->organization;
            $reference = $this->referenceGenerator->generate($organization);

            $ticket = Ticket::create([
                ...$data,
                'organization_id' => $organization?->id ?? $creator->organization_id,
                'reference' => $reference,
                'created_by' => $creator->id,
                'type' => $data['type'] ?? 'internal',
            ]);

            $ticket->statusHistories()->create([
                'old_status' => null,
                'new_status' => $ticket->status,
                'changed_by' => $creator->id,
                'notes' => 'Estado inicial do pedido.',
            ]);

            $this->activityLogger->log(
                subject: $ticket,
                action: 'ticket.created',
                user: $creator,
                organization: $organization,
                newValues: $ticket->only([
                    'reference',
                    'title',
                    'status',
                    'priority',
                    'source',
                    'visibility',
                    'assigned_to',
                    'due_date',
                ]),
                description: 'Pedido criado.',
            );

            return $ticket;
        });
    }
}
