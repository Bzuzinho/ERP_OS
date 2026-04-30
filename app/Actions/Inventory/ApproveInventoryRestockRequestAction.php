<?php

namespace App\Actions\Inventory;

use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class ApproveInventoryRestockRequestAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(InventoryRestockRequest $restockRequest, User $performedBy, float $quantityApproved, ?string $notes = null): InventoryRestockRequest
    {
        $restockRequest->status = 'approved';
        $restockRequest->approved_by = $performedBy->id;
        $restockRequest->approved_at = now();
        $restockRequest->quantity_approved = $quantityApproved;
        $restockRequest->notes = $notes;
        $restockRequest->save();

        $this->activityLogger->log(
            subject: $restockRequest,
            action: 'inventory.restock.approved',
            user: $performedBy,
            organization: $restockRequest->organization,
            newValues: ['status' => 'approved', 'quantity_approved' => $quantityApproved],
            description: 'Pedido de reposicao aprovado.',
        );

        return $restockRequest;
    }
}
