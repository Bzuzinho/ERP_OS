<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunicationsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get unread notifications
        $notifications = Notification::query()
            ->whereHas('recipients', fn ($q) => $q
                ->where('user_id', $user->id)
                ->whereNull('read_at')
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Mobile/Communications/Index', [
            'notifications' => $notifications,
        ]);
    }
}
