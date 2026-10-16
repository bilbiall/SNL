<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** GameSet is for admins only — a logged-in non-admin gets a plain 403. */
class GamesetMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->is_admin) {
            abort(403, 'GameSet is for admins only.');
        }

        return $next($request);
    }
}
