<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('instructor can manage own course via elearning permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('instructor');

    $course = Course::create([
        'title' => 'Test Course',
        'slug' => 'test-course-instructor-own',
        'created_by' => $user->id,
        'status' => 'draft',
        'is_paid' => false,
    ]);

    expect($user->can('manage', $course))->toBeTrue();
});

test('lms moderator can manage any course', function () {
    $user = User::factory()->create();
    $user->assignRole('lms_moderator');

    $course = Course::create([
        'title' => 'Moderator Course',
        'slug' => 'moderator-course',
        'created_by' => User::factory()->create()->id,
        'status' => 'draft',
        'is_paid' => false,
    ]);

    expect($user->can('manage', $course))->toBeTrue();
});

test('writer does not implicitly manage course', function () {
    $user = User::factory()->create();
    $user->assignRole('writer');

    $course = Course::create([
        'title' => 'Writer Course',
        'slug' => 'writer-course',
        'created_by' => $user->id,
        'status' => 'draft',
        'is_paid' => false,
    ]);

    expect($user->can('manage', $course))->toBeFalse();
});

test('subscriber and learner can enroll only into free published course', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $learner = User::factory()->create();
    $learner->assignRole('learner');

    $course = Course::create([
        'title' => 'Free Published Course',
        'slug' => 'free-published-course',
        'status' => 'published',
        'is_paid' => false,
    ]);

    expect($subscriber->can('enroll', $course))->toBeTrue();
    expect($learner->can('enroll', $course))->toBeTrue();
});

test('cannot enroll into unpublished or paid course', function () {
    $user = User::factory()->create();
    $user->assignRole('learner');

    $unpublished = Course::create([
        'title' => 'Unpublished Course',
        'slug' => 'unpublished-course',
        'status' => 'draft',
        'is_paid' => false,
    ]);

    $paid = Course::create([
        'title' => 'Paid Course',
        'slug' => 'paid-course',
        'status' => 'published',
        'is_paid' => true,
    ]);

    expect($user->can('enroll', $unpublished))->toBeFalse();
    expect($user->can('enroll', $paid))->toBeFalse();
});
