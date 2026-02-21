<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function manage(User $user, Course $course): bool
    {
        if ($user->can('elearning.course.edit')) {
            return true;
        }

        if ($user->can('elearning.course.edit_own')) {
            return $course->created_by === $user->id;
        }

        return false;
    }

    public function enroll(User $user, Course $course): bool
    {
        if (! $user->can('elearning.participant.enroll') && ! $user->can('portal.enroll.course')) {
            return false;
        }

        if ($course->status !== 'published') {
            return false;
        }

        return ! $course->is_paid;
    }
}
