<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Support\OrganizationScope;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * @param  array<int, \App\Models\User|int>|Collection<int, \App\Models\User|int>  $users
     * @param  array<string, mixed>  $payload
     */
    public function createForUsers(array|Collection $users, array $payload): Notification
    {
        $recipientIds = collect($users)
            ->map(function ($user) use ($payload) {
                if ($user instanceof User) {
                    $organizationId = $payload['organization_id'] ?? null;

                    if ($organizationId !== null && ! $user->hasRole('super_admin') && (int) $user->organization_id !== (int) $organizationId) {
                        return null;
                    }

                    return $user->id;
                }

                return (int) $user;
            })
            ->filter()
            ->unique()
            ->values();

        $notifiable = $payload['notifiable'] ?? null;

        $notification = Notification::query()->create([
            'organization_id' => $payload['organization_id'] ?? null,
            'type' => $payload['type'] ?? 'system',
            'title' => $payload['title'] ?? 'Notificacao',
            'message' => $payload['message'] ?? null,
            'notifiable_type' => $notifiable ? $notifiable->getMorphClass() : null,
            'notifiable_id' => $notifiable?->getKey(),
            'action_url' => $payload['action_url'] ?? null,
            'priority' => $payload['priority'] ?? 'normal',
            'data' => $payload['data'] ?? null,
            'created_by' => $payload['created_by'] ?? null,
        ]);

        if ($recipientIds->isNotEmpty()) {
            $now = now();
            $notification->recipients()->insert(
                $recipientIds
                    ->map(fn (int $userId) => [
                        'notification_id' => $notification->id,
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all()
            );
        }

        return $notification;
    }

    public function markAsRead(NotificationRecipient $recipient): void
    {
        if ($recipient->read_at) {
            return;
        }

        $now = now();

        $recipient->forceFill([
            'seen_at' => $recipient->seen_at ?? $now,
            'read_at' => $now,
        ])->save();
    }

    public function markAllAsRead(User $user): int
    {
        $now = now();

        return NotificationRecipient::query()
            ->where('user_id', $user->id)
            ->when(! OrganizationScope::canBypassOrganizationScope($user), fn ($query) => $query->whereHas('notification', fn ($notificationQuery) => $notificationQuery->where('organization_id', $user->organization_id)))
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->update([
                'seen_at' => $now,
                'read_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function getUnreadCount(User $user): int
    {
        return NotificationRecipient::query()
            ->where('user_id', $user->id)
            ->when(! OrganizationScope::canBypassOrganizationScope($user), fn ($query) => $query->whereHas('notification', fn ($notificationQuery) => $notificationQuery->where('organization_id', $user->organization_id)))
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->count();
    }

    public function getRecentForUser(User $user, int $limit = 10): Collection
    {
        return NotificationRecipient::query()
            ->with(['notification'])
            ->where('user_id', $user->id)
            ->when(! OrganizationScope::canBypassOrganizationScope($user), fn ($query) => $query->whereHas('notification', fn ($notificationQuery) => $notificationQuery->where('organization_id', $user->organization_id)))
            ->whereNull('archived_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
