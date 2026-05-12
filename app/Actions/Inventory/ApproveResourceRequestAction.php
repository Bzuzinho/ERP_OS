<?php

namespace App\Actions\Inventory;

use App\Models\ResourceRequest;
use App\Models\User;
use App\Services\Notifications\ResourceRequestNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveResourceRequestAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly ResourceRequestNotificationService $notificationService,
    ) {
    }

    public function execute(User $approver, ResourceRequest $resourceRequest, array $data): ResourceRequest
    {
        return DB::transaction(function () use ($approver, $resourceRequest, $data) {
            // Validate user can approve in organization
            if ($approver->organization_id !== $resourceRequest->organization_id) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            // Validate status
            if ($resourceRequest->status !== 'requested') {
                throw new RuntimeException("Nao e possivel aprovar uma requisicao com status '{$resourceRequest->status}'.");
            }

            $oldStatus = $resourceRequest->status;

            // Update items with approved quantities
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemId => $itemData) {
                    $approvedQuantity = (float) ($itemData['quantity_approved'] ?? 0);
                    
                    $item = $resourceRequest->items()->findOrFail($itemId);
                    
                    // Validate approved quantity doesn't exceed requested
                    if ($approvedQuantity > (float) $item->quantity_requested) {
                        throw new RuntimeException('Quantidade aprovada nao pode exceder quantidade requisitada.');
                    }

                    if ($approvedQuantity > 0) {
                        $item->update(['quantity_approved' => $approvedQuantity]);
                    }
                }
            }

            // Update request
            $resourceRequest->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.approved',
                user: $approver,
                organization: $resourceRequest->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $resourceRequest->status, 'approved_by' => $approver->id],
                description: 'Requisicao de recursos aprovada.',
            );

            $this->notificationService->notifyApproved($resourceRequest, $approver);

            return $resourceRequest;
        });
    }
}
