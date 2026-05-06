<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use App\Support\OrganizationScope;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', NotificationRecipient::class);

        $filter = $request->string('filter')->toString() ?: 'all';

        $notifications = NotificationRecipient::query()
            ->with('notification')
            ->where('user_id', $request->user()->id)
            ->when(! OrganizationScope::canBypassOrganizationScope($request->user()), fn ($query) => $query->whereHas('notification', fn ($notificationQuery) => $notificationQuery->where('organization_id', $request->user()->organization_id)))
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($filter === 'archived', fn ($query) => $query->whereNotNull('archived_at'))
            ->when($filter !== 'archived', fn ($query) => $query->whereNull('archived_at'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(function (NotificationRecipient $recipient) {
                $actionUrl = $recipient->notification?->action_url;

                if ($actionUrl && str_starts_with($actionUrl, '/admin/tickets/')) {
                    $recipient->notification->action_url = str_replace('/admin/tickets/', '/portal/tickets/', $actionUrl);
                }

                return $recipient;
            })
            ->withQueryString();

        return Inertia::render('Portal/Notifications/Index', [
            'notifications' => $notifications,
            'filter' => $filter,
        ]);
    }
}
