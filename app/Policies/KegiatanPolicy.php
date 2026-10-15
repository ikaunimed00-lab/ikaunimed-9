<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;

class KegiatanPolicy
{
    private function canManageAll(User $user): bool
    {
        return $user->hasSystemRole(['super_admin', 'admin']);
    }

    public function viewAny(User $user): bool
    {
        return $this->canManageAll($user) || $user->hasTocPermission('toc.kegiatan.update');
    }

    public function view(User $user, Kegiatan $kegiatan): bool
    {
        return $this->canManageAll($user) || $this->update($user, $kegiatan);
    }

    public function create(User $user): bool
    {
        return $this->canManageAll($user);
    }

    public function update(User $user, Kegiatan $kegiatan): bool
    {
        if ($this->canManageAll($user)) {
            return true;
        }

        return $kegiatan->isTeacherOfChampions() && $user->hasTocPermission('toc.kegiatan.update');
    }

    public function delete(User $user, Kegiatan $kegiatan): bool
    {
        return $this->canManageAll($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManageAll($user);
    }
}
