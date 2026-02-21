<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function createTestCourse(array $overrides = []): Course
{
    return Course::create(array_merge([
        'title' => 'Test Course',
        'slug' => 'test-course-' . uniqid(),
        'status' => 'published',
        'is_paid' => false,
    ], $overrides));
}

test('instructor can view and manage enrollment for own course', function () {
    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    $learner = User::factory()->create();
    $learner->assignRole('learner');

    $course = createTestCourse(['created_by' => $instructor->id]);

    $enrollment = Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect($instructor->can('view', $enrollment))->toBeTrue();
    expect($instructor->can('manage', $enrollment))->toBeTrue();
});

test('instructor cannot manage enrollment for other instructors course', function () {
    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    $otherInstructor = User::factory()->create();
    $otherInstructor->assignRole('instructor');

    $learner = User::factory()->create();
    $learner->assignRole('learner');

    $course = createTestCourse(['created_by' => $otherInstructor->id]);

    $enrollment = Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect($instructor->can('view', $enrollment))->toBeFalse();
    expect($instructor->can('manage', $enrollment))->toBeFalse();
});

test('lms moderator can view and manage any enrollment', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('lms_moderator');

    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    $learner = User::factory()->create();
    $learner->assignRole('learner');

    $course = createTestCourse(['created_by' => $instructor->id]);

    $enrollment = Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect($moderator->can('view', $enrollment))->toBeTrue();
    expect($moderator->can('manage', $enrollment))->toBeTrue();
    expect($moderator->can('cancel', $enrollment))->toBeTrue();
});

test('learner can view and cancel own enrollment but not manage', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');

    $course = createTestCourse();

    $enrollment = Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect($learner->can('view', $enrollment))->toBeTrue();
    expect($learner->can('cancel', $enrollment))->toBeTrue();
    expect($learner->can('manage', $enrollment))->toBeFalse();
});

test('subscriber without lms role can only view own enrollment if exists', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $course = createTestCourse();

    $ownEnrollment = Enrollment::create([
        'user_id' => $subscriber->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    $otherUser = User::factory()->create();
    $otherEnrollment = Enrollment::create([
        'user_id' => $otherUser->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect($subscriber->can('view', $ownEnrollment))->toBeTrue();
    expect($subscriber->can('cancel', $ownEnrollment))->toBeTrue();
    expect($subscriber->can('manage', $ownEnrollment))->toBeFalse();

    expect($subscriber->can('view', $otherEnrollment))->toBeFalse();
    expect($subscriber->can('manage', $otherEnrollment))->toBeFalse();
});

