<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictPanitiaBerkasPanel
{
    /**
     * Panitia berkas hanya boleh membuka menu Berkas Finalis ToC.
     * /admin diarahkan ke halaman itu; Livewire/Filament AJAX tetap diizinkan.
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

        if ($this->isAllowedPath($path)) {
            return $next($request);
        }

        if ($path === 'admin' || $path === 'admin/') {
            return redirect('/admin/berkas-finalis-toc');
        }

        abort(403, 'Anda tidak punya akses.');
    }

    private function isAllowedPath(string $path): bool
    {
        if ($path === 'admin/berkas-finalis-toc' || str_starts_with($path, 'admin/berkas-finalis-toc/')) {
            return true;
        }

        if ($path === 'admin/logout' || str_starts_with($path, 'admin/logout')) {
            return true;
        }

        // Livewire update / upload dipakai Filament di luar prefix resource.
        if ($path === 'livewire/update' || str_starts_with($path, 'livewire/')) {
            return true;
        }

        // Unduh/lihat/terima/tolak berkas (bukan halaman Filament).
        if (str_starts_with($path, 'admin/toc/berkas/')) {
            return true;
        }

        return false;
    }
}
