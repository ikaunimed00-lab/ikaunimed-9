<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHomepagePreviewAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = (string) config('homepage.preview_token');
        $providedToken = (string) $request->query('preview', '');

        if ($configuredToken !== '' && $providedToken !== '' && hash_equals($configuredToken, $providedToken)) {
            return $next($request);
        }

        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        if ($request->user()->hasSystemRole(['super_admin', 'admin'])) {
            return $next($request);
        }

        abort(403);
    }
}
