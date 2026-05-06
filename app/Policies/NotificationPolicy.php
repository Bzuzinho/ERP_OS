<?php

namespace App\Policies;

use App\Models\NotificationRecipient;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('notifications.view');
    }

    public function view(User $user, NotificationRecipient $recipient): bool
    {
        return $user->can('notifications.view') && (int) $recipient->user_id === (int) $user->id;
    }

    public function markRead(User $user, NotificationRecipient $recipient): bool
    {
        return $this->view($user, $recipient);
    }

    public function markAllRead(User $user): bool
    {
        return $user->can('notifications.view');
    }
}
