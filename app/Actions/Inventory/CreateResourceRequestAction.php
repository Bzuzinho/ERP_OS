<?php

namespace App\Actions\Inventory;

use App\Models\Organization;
use App\Models\ResourceRequest;
use App\Models\ResourceRequestItem;
use App\Models\InventoryItem;
use App\Models\User;
use App\Services\Notifications\ResourceRequestNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateResourceRequestAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly ResourceRequestNotificationService $notificationService,
    ) {
    }

    public function execute(User $requestedBy, array $data): ResourceRequest
    {
        return DB::transaction(function () use ($requestedBy, $data) {
            // Validate organization access
            if ($requestedBy->organization_id !== $data['organization_id']) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            $organization = Organization::findOrFail($data['organization_id']);

            // Validate requestable if provided
            if (isset($data['requestable_type']) && isset($data['requestable_id'])) {
                $requestableClass = $data['requestable_type'];
                $requestable = $requestableClass::findOrFail($data['requestable_id']);

                if ($requestable->organization_id !== $organization->id) {
                    throw new RuntimeException('Recurso requisitavel nao pertence a esta organizacao.');
                }
            }

            // Create request
            $resourceRequest = ResourceRequest::create([
                'organization_id' => $organization->id,
                'requested_by' => $requestedBy->id,
                'status' => 'requested',
                'requestable_type' => $data['requestable_type'] ?? null,
                'requestable_id' => $data['requestable_id'] ?? null,
                'title' => $data['title'],
                'purpose' => $data['purpose'] ?? null,
                'needed_from' => $data['needed_from'] ?? null,
                'needed_until' => $data['needed_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Add items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $inventoryItem = InventoryItem::findOrFail($itemData['inventory_item_id']);

                    // Validate item belongs to organization
                    if ($inventoryItem->organization_id !== $organization->id) {
                        throw new RuntimeException('Item de inventario nao pertence a esta organizacao.');
                    }

                    // Validate item is active
                    if (!$inventoryItem->is_active || $inventoryItem->status !== 'active') {
                        throw new RuntimeException("Item '{$inventoryItem->name}' nao esta ativo.");
                    }

                    // Validate quantity
                    $quantity = (float) $itemData['quantity_requested'];
                    if ($quantity <= 0) {
                        throw new RuntimeException('Quantidade tem de ser maior que zero.');
                    }

                    ResourceRequestItem::create([
                        'resource_request_id' => $resourceRequest->id,
                        'inventory_item_id' => $inventoryItem->id,
                        'quantity_requested' => $quantity,
                    ]);
                }
            }

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.created',
                user: $requestedBy,
                organization: $organization,
                newValues: $resourceRequest->only(['title', 'status', 'needed_from', 'needed_until']),
                description: 'Requisicao de recursos criada.',
            );

            $this->notificationService->notifyCreated($resourceRequest, $requestedBy);

            return $resourceRequest;
        });
    }
}
