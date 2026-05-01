<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->can('admin.access')) {
            abort(403, 'Não tem permissão para aceder à área de administração.');
        }

        return $next($request);
    }
}
