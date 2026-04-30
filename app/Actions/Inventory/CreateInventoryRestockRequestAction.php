<?php

namespace App\Actions\Inventory;

use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CreateInventoryRestockRequestAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $performedBy, array $data): InventoryRestockRequest
    {
        $request = InventoryRestockRequest::create([
            ...$data,
            'organization_id' => $performedBy->organization_id,
            'requested_by' => $performedBy->id,
            'status' => 'requested',
        ]);

        $this->activityLogger->log(
            subject: $request,
            action: 'inventory.restock.requested',
            user: $performedBy,
            organization: $request->organization,
            newValues: $request->only(['inventory_item_id', 'quantity_requested', 'status']),
            description: 'Pedido de reposicao criado.',
        );

        return $request;
    }
}
