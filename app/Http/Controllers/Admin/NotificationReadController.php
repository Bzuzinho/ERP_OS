<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationReadController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function store(Request $request, NotificationRecipient $notificationRecipient): RedirectResponse
    {
        $this->authorize('markRead', $notificationRecipient);

        $this->notificationService->markAsRead($notificationRecipient);

        return back();
    }

    public function markAll(Request $request): RedirectResponse
    {
        $this->authorize('markAllRead', NotificationRecipient::class);

        $this->notificationService->markAllAsRead($request->user());

        return back();
    }
}
