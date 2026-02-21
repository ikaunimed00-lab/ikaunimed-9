<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('instructor has expected elearning permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('instructor');

    expect($user->can('elearning.course.view'))->toBeTrue();
    expect($user->can('elearning.course.view_own'))->toBeTrue();
    expect($user->can('elearning.course.create'))->toBeTrue();
    expect($user->can('elearning.course.edit'))->toBeTrue();
    expect($user->can('elearning.course.edit_own'))->toBeTrue();
    expect($user->can('elearning.course.publish'))->toBeTrue();
    expect($user->can('elearning.course.unpublish'))->toBeTrue();
    expect($user->can('elearning.enrollment.view'))->toBeTrue();
    expect($user->can('elearning.enrollment.view_own_course'))->toBeTrue();
    expect($user->can('elearning.enrollment.manage'))->toBeTrue();

    expect($user->can('elearning.participant.enroll'))->toBeFalse();
    expect($user->can('elearning.participant.access_material'))->toBeFalse();
});

test('learner has only participant permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('learner');

    expect($user->can('elearning.participant.enroll'))->toBeTrue();
    expect($user->can('elearning.participant.access_material'))->toBeTrue();
    expect($user->can('elearning.participant.track_progress'))->toBeTrue();

    expect($user->can('elearning.course.create'))->toBeFalse();
    expect($user->can('elearning.course.edit'))->toBeFalse();
    expect($user->can('elearning.course.publish'))->toBeFalse();
    expect($user->can('elearning.enrollment.manage'))->toBeFalse();
});

test('lms moderator has global course and enrollment permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('lms_moderator');

    expect($user->can('elearning.course.view'))->toBeTrue();
    expect($user->can('elearning.course.view_any'))->toBeTrue();
    expect($user->can('elearning.course.edit'))->toBeTrue();
    expect($user->can('elearning.course.unpublish'))->toBeTrue();
    expect($user->can('elearning.course.archive'))->toBeTrue();

    expect($user->can('elearning.enrollment.view'))->toBeTrue();
    expect($user->can('elearning.enrollment.view_any'))->toBeTrue();
    expect($user->can('elearning.enrollment.manage'))->toBeTrue();
    expect($user->can('elearning.enrollment.cancel'))->toBeTrue();

    expect($user->can('elearning.participant.enroll'))->toBeFalse();
});

test('subscriber without lms role has no elearning permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    expect($user->can('elearning.course.create'))->toBeFalse();
    expect($user->can('elearning.course.edit'))->toBeFalse();
    expect($user->can('elearning.course.publish'))->toBeFalse();
    expect($user->can('elearning.enrollment.manage'))->toBeFalse();
    expect($user->can('elearning.participant.enroll'))->toBeFalse();
    expect($user->can('elearning.participant.access_material'))->toBeFalse();
});

