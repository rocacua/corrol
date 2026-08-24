<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Si no está autenticado o no es admin, simulamos que la página NO existe (404)
        if (!$user || !$user->is_admin) {
            abort(404);
        }

        return $next($request);
    }
}
