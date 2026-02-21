<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip jika user tidak login
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Check apakah profil alumni sudah lengkap
        if (!$this->isProfileComplete($user)) {
            return redirect()->route('profile.edit')->with('warning', 'Lengkapi profil alumni Anda terlebih dahulu');
        }

        return $next($request);
    }

    /**
     * Cek apakah profil alumni sudah lengkap
     */
    private function isProfileComplete($user): bool
    {
        // Field wajib minimal untuk mengakses fitur alumni (LMS, Shop, dsb.)
        $requiredFields = [
            'wa',
            'nik',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat_lengkap',
        ];

        foreach ($requiredFields as $field) {
            if (empty($user->{$field})) {
                return false;
            }
        }

        return true;
    }
}
