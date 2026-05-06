<?php

namespace App\Http\Middleware;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'avatar_path' => $user->avatar_path,
                    'organization' => $user->organization ? [
                        'id' => $user->organization->id,
                        'name' => $user->organization->name,
                        'slug' => $user->organization->slug,
                        'logo_path' => $user->organization->logo_path,
                    ] : null,
                ] : null,
                'can' => [
                    'accessAdmin' => $user?->can('admin.access') ?? false,
                ],
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'notifications' => $user ? [
                'unread_count' => $this->notificationService->getUnreadCount($user),
                'recent' => $this->notificationService
                    ->getRecentForUser($user, 10)
                    ->map(fn ($recipient) => [
                        'id' => $recipient->id,
                        'notification_id' => $recipient->notification_id,
                        'title' => $recipient->notification?->title,
                        'message' => $recipient->notification?->message,
                        'priority' => $recipient->notification?->priority ?? 'normal',
                        'type' => $recipient->notification?->type,
                        'action_url' => $recipient->notification?->action_url,
                        'read_at' => $recipient->read_at?->toISOString(),
                        'created_at' => $recipient->created_at?->toISOString(),
                        'created_at_human' => $recipient->created_at?->diffForHumans(),
                    ])
                    ->values(),
            ] : [
                'unread_count' => 0,
                'recent' => [],
            ],
        ];
    }
}
