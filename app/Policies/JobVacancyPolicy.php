<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;

class JobVacancyPolicy
{
    public function view(User $user, JobVacancy $job): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $job->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, JobVacancy $job): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $job->user_id === $user->id;
    }

    public function delete(User $user, JobVacancy $job): bool
    {
        if ($user->can('cms.job.publish')) {
            return true;
        }

        return $job->user_id === $user->id;
    }

    public function publish(User $user, JobVacancy $job): bool
    {
        return $user->can('cms.job.publish');
    }
}

