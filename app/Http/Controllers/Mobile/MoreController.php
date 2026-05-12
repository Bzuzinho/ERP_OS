<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MoreController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Mobile/More/Index', [
            'user' => $user->only(['id', 'name', 'email', 'avatar_url']),
        ]);
    }
}
