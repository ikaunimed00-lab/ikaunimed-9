<?php

namespace App\Policies;

use App\Models\Scholarship;
use App\Models\User;

class ScholarshipPolicy
{
    public function view(User $user, Scholarship $scholarship): bool
    {
        if ($user->can('cms.scholarship.publish')) {
            return true;
        }

        return $scholarship->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Scholarship $scholarship): bool
    {
        if ($user->can('cms.scholarship.publish')) {
            return true;
        }

        return $scholarship->user_id === $user->id;
    }

    public function delete(User $user, Scholarship $scholarship): bool
    {
        if ($user->can('cms.scholarship.publish')) {
            return true;
        }

        return $scholarship->user_id === $user->id;
    }
}

