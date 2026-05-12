<?php

namespace App\Actions\Inventory;

use App\Models\ResourceRequest;
use App\Models\User;
use App\Services\Notifications\ResourceRequestNotificationService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PrepareResourceRequestAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly ResourceRequestNotificationService $notificationService,
    ) {
    }

    public function execute(User $user, ResourceRequest $resourceRequest): ResourceRequest
    {
        return DB::transaction(function () use ($user, $resourceRequest) {
            // Validate user can prepare in organization
            if ($user->organization_id !== $resourceRequest->organization_id) {
                throw new RuntimeException('Utilizador nao tem acesso a esta organizacao.');
            }

            // Validate status
            if ($resourceRequest->status !== 'approved') {
                throw new RuntimeException("Nao e possivel preparar uma requisicao com status '{$resourceRequest->status}'.");
            }

            $oldStatus = $resourceRequest->status;

            $resourceRequest->update([
                'status' => 'prepared',
                'prepared_at' => now(),
            ]);

            $this->activityLogger->log(
                subject: $resourceRequest,
                action: 'resource_request.prepared',
                user: $user,
                organization: $resourceRequest->organization,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $resourceRequest->status],
                description: 'Requisicao de recursos preparada.',
            );

            $this->notificationService->notifyPrepared($resourceRequest, $user);

            return $resourceRequest;
        });
    }
}
