<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($user->can('elearning.enrollment.view_any')) {
            return true;
        }

        if ($user->can('elearning.enrollment.view_own_course')) {
            return $enrollment->course && $enrollment->course->created_by === $user->id;
        }

        if ($user->id === $enrollment->user_id) {
            return true;
        }

        return false;
    }

    public function manage(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('elearning.enrollment.manage')) {
            return false;
        }

        if ($user->can('elearning.enrollment.view_any')) {
            return true;
        }

        if ($user->can('elearning.enrollment.view_own_course')) {
            return $enrollment->course && $enrollment->course->created_by === $user->id;
        }

        return false;
    }

    public function cancel(User $user, Enrollment $enrollment): bool
    {
        if ($user->can('elearning.enrollment.cancel')) {
            return true;
        }

        if ($user->id === $enrollment->user_id) {
            return true;
        }

        return false;
    }
}

