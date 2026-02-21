<?php

namespace App\Policies;

use App\Models\Partnership;
use App\Models\User;

class PartnershipPolicy
{
    public function view(User $user, Partnership $partnership): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $partnership->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Partnership $partnership): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $partnership->user_id === $user->id;
    }

    public function delete(User $user, Partnership $partnership): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $partnership->user_id === $user->id;
    }

    public function moderate(User $user, Partnership $partnership): bool
    {
        return $user->can('cms.job.publish');
    }
}

