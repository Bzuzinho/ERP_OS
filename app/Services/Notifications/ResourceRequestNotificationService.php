<?php

namespace App\Services\Notifications;

use App\Models\ResourceRequest;
use App\Models\User;

class ResourceRequestNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function notifyCreated(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_created',
            'title' => 'Nova requisicao de recursos',
            'message' => 'Foi criada uma nova requisicao de recursos para analise.',
            'priority' => 'normal',
        ], $actor);
    }

    public function notifyApproved(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_approved',
            'title' => 'Requisicao aprovada',
            'message' => 'A requisicao de recursos foi aprovada e esta pronta para preparacao.',
            'priority' => 'high',
        ], $actor);
    }

    public function notifyRejected(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_rejected',
            'title' => 'Requisicao rejeitada',
            'message' => 'A requisicao de recursos foi rejeitada.',
            'priority' => 'high',
        ], $actor);
    }

    public function notifyPrepared(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_prepared',
            'title' => 'Material preparado',
            'message' => 'O material da requisicao foi preparado para entrega.',
            'priority' => 'normal',
        ], $actor);
    }

    public function notifyDelivered(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_delivered',
            'title' => 'Material entregue',
            'message' => 'A requisicao de recursos foi entregue.',
            'priority' => 'high',
        ], $actor);
    }

    public function notifyPartiallyReturned(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_partially_returned',
            'title' => 'Devolucao parcial',
            'message' => 'A requisicao de recursos recebeu devolucao parcial.',
            'priority' => 'normal',
        ], $actor);
    }

    public function notifyReturned(ResourceRequest $resourceRequest, ?User $actor = null): void
    {
        $this->notifyOrganizationStaff($resourceRequest, [
            'type' => 'resource_request_returned',
            'title' => 'Devolucao concluida',
            'message' => 'A requisicao de recursos foi devolvida na totalidade.',
            'priority' => 'normal',
        ], $actor);
    }

    private function notifyOrganizationStaff(ResourceRequest $resourceRequest, array $payload, ?User $actor = null): void
    {
        $recipients = User::query()
            ->where('organization_id', $resourceRequest->organization_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'admin_junta', 'executivo', 'administrativo', 'armazem']))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $this->notificationService->createForUsers($recipients, [
            'organization_id' => $resourceRequest->organization_id,
            'type' => $payload['type'],
            'title' => $payload['title'],
            'message' => $payload['message'],
            'notifiable' => $resourceRequest,
            'action_url' => route('admin.resource-requests.show', $resourceRequest, false),
            'priority' => $payload['priority'] ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'resource_request_id' => $resourceRequest->id,
                'status' => $resourceRequest->status,
            ],
        ]);
    }
}
