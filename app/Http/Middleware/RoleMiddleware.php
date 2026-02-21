<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! $request->user() || ! $request->user()->hasSystemRole($roles)) {
            abort(403, 'Anda tidak punya akses.');
        }

        return $next($request);
    }
}
