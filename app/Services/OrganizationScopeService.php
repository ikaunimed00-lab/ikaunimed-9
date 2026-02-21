<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OrganizationScopeService
{
    /**
     * Terapkan scope organisasi pada query berdasarkan user yang sedang login.
     * 
     * Aturan:
     * 1. Central Admin (isCentralAdmin) -> Bisa melihat semua data (tanpa scope).
     * 2. User dengan organization_id -> Hanya bisa melihat data milik organisasinya sendiri.
     */
    public function applyScope(Builder $query, User $user): Builder
    {
        if ($user->isCentralAdmin()) {
            return $query;
        }

        if ($user->organization_id) {
            return $query->where('organization_id', $user->organization_id);
        }

        // Jika bukan admin pusat dan tidak punya organisasi (failsafe)
        // Batasi query agar tidak menampilkan data sensitif
        return $query->whereRaw('1 = 0');
    }

    /**
     * Mendapatkan data default untuk input field (misal: organization_id).
     */
    public function getInitialData(User $user): array
    {
        return [
            'organization_id' => $user->organization_id,
        ];
    }
}
