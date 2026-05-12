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

class ReturnResourceRequestAction
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
            // Validate user can return in organization
            if ($user->organization_id !== $resourceRequest->organization_id) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            // Validate status
            if (!in_array($resourceRequest->status, ['delivered', 'partially_returned'])) {
                throw new RuntimeException("Nao e possivel devolver uma requisicao com status '{$resourceRequest->status}'.");
            }

            $oldStatus = $resourceRequest->status;
            // Process returns
            if (!isset($data['items']) || !is_array($data['items'])) {
                throw new RuntimeException('Itens para devolucao nao fornecidos.');
            }

            foreach ($data['items'] as $itemId => $itemData) {
                $returnedQuantity = (float) ($itemData['quantity_returned'] ?? 0);
                
                if ($returnedQuantity <= 0) {
                    continue;
                }

                /** @var ResourceRequestItem $requestItem */
                $requestItem = $resourceRequest->items()->findOrFail($itemId);
                $inventoryItem = $requestItem->inventoryItem;

                $deliveredQuantity = (float) ($requestItem->quantity_delivered ?? 0);
                $alreadyReturned = (float) ($requestItem->quantity_returned ?? 0);
                $remainingToReturn = $deliveredQuantity - $alreadyReturned;

                // Validate return doesn't exceed delivered
                if ($returnedQuantity > $remainingToReturn) {
                    throw new RuntimeException("Quantidade devolvida nao pode exceder quantidade entregue para item '{$inventoryItem->name}'.");
                }

                $newReturnedQuantity = $alreadyReturned + $returnedQuantity;
                $requestItem->update(['quantity_returned' => $newReturnedQuantity]);

                $this->resourceRequestStockService->return($resourceRequest, $requestItem, $user, $returnedQuantity);
            }

            $resourceRequest->load('items');

            $hasDelivered = $resourceRequest->items->contains(fn (ResourceRequestItem $item) => (float) ($item->quantity_delivered ?? 0) > 0);
            $hasPendingReturn = $resourceRequest->items->contains(fn (ResourceRequestItem $item) => (float) ($item->quantity_delivered ?? 0) > (float) ($item->quantity_returned ?? 0));

            if (! $hasDelivered) {
                throw new RuntimeException('Nao existem quantidades entregues para devolver.');
            }

            $newStatus = $hasPendingReturn ? 'partially_returned' : 'returned';

            $resourceRequest->update([
                'status' => $newStatus,
                'returned_at' => $newStatus === 'returned' ? now() : null,
            ]);

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.returned',
                user: $user,
                organization: $resourceRequest->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $resourceRequest->status],
                description: "Requisicao de recursos {$newStatus}.",
            );

            if ($newStatus === 'returned') {
                $this->notificationService->notifyReturned($resourceRequest, $user);
            } else {
                $this->notificationService->notifyPartiallyReturned($resourceRequest, $user);
            }

            return $resourceRequest;
        });
    }
}
