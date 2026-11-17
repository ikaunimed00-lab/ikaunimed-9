<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictPanitiaBerkasPanel
{
    /**
     * Panitia berkas hanya boleh membuka menu Berkas Finalis ToC dan keluar dari panel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isPanitiaBerkasOnly()) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if ($path === 'admin/berkas-finalis-toc' || str_starts_with($path, 'admin/berkas-finalis-toc/')) {
            return $next($request);
        }

        if ($path === 'admin/logout') {
            return $next($request);
        }

        abort(403);
    }
}
