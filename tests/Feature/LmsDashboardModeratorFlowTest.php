<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('lms moderator can access dashboard courses index', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('lms_moderator');

    Course::create([
        'title' => 'Course B',
        'slug' => 'course-b',
        'status' => 'draft',
        'is_paid' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    actingAs($moderator);

    $response = get(route('dashboard.courses.index'));

    $response->assertStatus(200);
});

test('lms moderator can access moderator courses dashboard', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('lms_moderator');

    Course::create([
        'title' => 'Course C',
        'slug' => 'course-c',
        'status' => 'published',
        'is_paid' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    actingAs($moderator);

    $response = get(route('dashboard.elearning.moderator.courses.index'));

    $response->assertStatus(200);
});

test('instructor cannot access moderator courses dashboard', function () {
    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    actingAs($instructor);

    $response = get(route('dashboard.elearning.moderator.courses.index'));

    $response->assertStatus(403);
});
