<?php

namespace App\Actions\Inventory;

use App\Models\ResourceRequest;
use App\Models\ResourceRequestItem;
use App\Models\User;
use App\Services\Inventory\ResourceRequestStockService;
use App\Services\Notifications\ResourceRequestNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliverResourceRequestAction
{
    public function __construct(
        private readonly ResourceRequestStockService $resourceRequestStockService,
        private readonly ActivityLogger $activityLogger,
        private readonly ResourceRequestNotificationService $notificationService,
    ) {
    }

    public function execute(User $user, ResourceRequest $resourceRequest, array $data): ResourceRequest
    {
        return DB::transaction(function () use ($user, $resourceRequest, $data) {
            // Validate user can deliver in organization
            if ($user->organization_id !== $resourceRequest->organization_id) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            // Validate status
            if ($resourceRequest->status !== 'prepared') {
                throw new RuntimeException("Nao e possivel entregar uma requisicao com status '{$resourceRequest->status}'.");
            }

            $oldStatus = $resourceRequest->status;

            // Process deliveries
            if (!isset($data['items']) || !is_array($data['items'])) {
                throw new RuntimeException('Itens para entrega nao fornecidos.');
            }

            foreach ($data['items'] as $itemId => $itemData) {
                $deliveredQuantity = (float) ($itemData['quantity_delivered'] ?? 0);
                
                if ($deliveredQuantity <= 0) {
                    continue; // Skip if no quantity to deliver
                }

                /** @var ResourceRequestItem $requestItem */
                $requestItem = $resourceRequest->items()->findOrFail($itemId);
                $inventoryItem = $requestItem->inventoryItem;

                // Validate delivery doesn't exceed approved
                if ($deliveredQuantity > (float) ($requestItem->quantity_approved ?? 0)) {
                    throw new RuntimeException("Quantidade entregue nao pode exceder quantidade aprovada para item '{$inventoryItem->name}'.");
                }

                $requestItem->update(['quantity_delivered' => $deliveredQuantity]);

                // If item is loanable, create InventoryLoan
                $this->resourceRequestStockService->deliver($resourceRequest, $requestItem, $user, $deliveredQuantity);
            }

            // Update request status
            $resourceRequest->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.delivered',
                user: $user,
                organization: $resourceRequest->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $resourceRequest->status],
                description: 'Requisicao de recursos entregue.',
            );

            $this->notificationService->notifyDelivered($resourceRequest, $user);

            return $resourceRequest;
        });
    }
}
