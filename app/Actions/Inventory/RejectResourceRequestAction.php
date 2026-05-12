<?php

namespace App\Actions\Inventory;

use App\Models\ResourceRequest;
use App\Models\User;
use App\Services\Notifications\ResourceRequestNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RejectResourceRequestAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly ResourceRequestNotificationService $notificationService,
    ) {
    }

    public function execute(User $rejector, ResourceRequest $resourceRequest, array $data): ResourceRequest
    {
        return DB::transaction(function () use ($rejector, $resourceRequest, $data) {
            // Validate user can reject in organization
            if ($rejector->organization_id !== $resourceRequest->organization_id) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            // Validate status
            if ($resourceRequest->status !== 'requested') {
                throw new RuntimeException("Nao e possivel rejeitar uma requisicao com status '{$resourceRequest->status}'.");
            }

            $oldStatus = $resourceRequest->status;

            $resourceRequest->update([
                'status' => 'rejected',
                'rejected_by' => $rejector->id,
                'rejected_at' => now(),
                'rejection_reason' => $data['rejection_reason'] ?? null,
            ]);

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.rejected',
                user: $rejector,
                organization: $resourceRequest->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $resourceRequest->status, 'rejection_reason' => $data['rejection_reason'] ?? null],
                description: 'Requisicao de recursos rejeitada.',
            );

            $this->notificationService->notifyRejected($resourceRequest, $rejector);

            return $resourceRequest;
        });
    }
}
