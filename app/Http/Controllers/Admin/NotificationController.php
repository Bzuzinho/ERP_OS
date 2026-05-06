<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
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
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($filter === 'archived', fn ($query) => $query->whereNotNull('archived_at'))
            ->when($filter !== 'archived', fn ($query) => $query->whereNull('archived_at'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'filter' => $filter,
        ]);
    }
}
