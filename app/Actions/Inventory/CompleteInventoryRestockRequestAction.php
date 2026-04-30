<?php

namespace App\Actions\Inventory;

use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CompleteInventoryRestockRequestAction
{
    public function __construct(
        private readonly RegisterInventoryMovementAction $registerMovementAction,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(InventoryRestockRequest $restockRequest, User $performedBy, ?string $notes = null): InventoryRestockRequest
    {
        return DB::transaction(function () use ($restockRequest, $performedBy, $notes) {
            $restockRequest->status = 'completed';
            $restockRequest->completed_at = now();
            $restockRequest->notes = $notes ?? $restockRequest->notes;
            $restockRequest->save();

            $item = $restockRequest->item()->firstOrFail();
            $quantity = (float) ($restockRequest->quantity_approved ?? $restockRequest->quantity_requested);

            $this->registerMovementAction->execute($item, $performedBy, [
                'movement_type' => 'restock',
                'quantity' => $quantity,
                'notes' => $notes ?? 'Reposicao concluida.',
            ]);

            $this->activityLogger->log(
                subject: $restockRequest,
                action: 'inventory.restock.completed',
                user: $performedBy,
                organization: $restockRequest->organization,
                newValues: ['status' => 'completed', 'completed_at' => $restockRequest->completed_at],
                description: 'Pedido de reposicao concluido.',
            );

            return $restockRequest;
        });
    }
}
