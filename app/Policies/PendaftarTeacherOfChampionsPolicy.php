<?php

namespace App\Policies;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;

class PendaftarTeacherOfChampionsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasTocPermission('toc.pendaftar.view');
    }

    public function view(User $user, PendaftarTeacherOfChampions $pendaftar): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PendaftarTeacherOfChampions $pendaftar): bool
    {
        return $user->hasTocPermission('toc.pendaftar.update');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasTocPermission('toc.pendaftar.update');
    }

    public function export(User $user): bool
    {
        return $user->hasTocPermission('toc.pendaftar.export');
    }

    /**
     * Unmasked email and WhatsApp are limited to users who may act on the registrant.
     */
    public function viewContact(User $user): bool
    {
        return $user->hasTocPermission('toc.pendaftar.update') || $user->hasTocPermission('toc.pendaftar.export');
    }

    /**
     * Deleting registrants is reserved for super_admin, which passes via Gate::before.
     */
    public function delete(User $user, PendaftarTeacherOfChampions $pendaftar): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
