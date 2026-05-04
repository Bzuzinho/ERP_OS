<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', \App\Models\User::class);

        return Inertia::render('Admin/Settings/Index');
    }
}
