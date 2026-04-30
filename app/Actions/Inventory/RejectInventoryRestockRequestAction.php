<?php

namespace App\Actions\Inventory;

use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class RejectInventoryRestockRequestAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(InventoryRestockRequest $restockRequest, User $performedBy, string $rejectionReason): InventoryRestockRequest
    {
        $restockRequest->status = 'rejected';
        $restockRequest->rejected_at = now();
        $restockRequest->rejection_reason = $rejectionReason;
        $restockRequest->save();

        $this->activityLogger->log(
            subject: $restockRequest,
            action: 'inventory.restock.rejected',
            user: $performedBy,
            organization: $restockRequest->organization,
            newValues: ['status' => 'rejected', 'rejection_reason' => $rejectionReason],
            description: 'Pedido de reposicao rejeitado.',
        );

        return $restockRequest;
    }
}
