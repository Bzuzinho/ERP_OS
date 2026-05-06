<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless(
            $user && (
                $user->can('settings.view')
                || $user->can('users.view')
                || $user->can('roles.view')
                || $user->can('service_areas.view')
                || $user->can('notifications.view')
            ),
            403
        );

        $cards = [
            'users' => $user->can('users.view'),
            'roles' => $user->can('roles.view'),
            'serviceAreas' => $user->can('service_areas.view'),
            'notifications' => $user->can('notifications.view'),
            'organization' => $user->can('settings.update'),
            'generalSettings' => false,
        ];

        return Inertia::render('Admin/Settings/Index', [
            'cards' => $cards,
        ]);
    }
}
