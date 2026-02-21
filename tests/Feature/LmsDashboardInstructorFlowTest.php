<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('instructor can access dashboard courses index', function () {
    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    Course::create([
        'title' => 'Course A',
        'slug' => 'course-a',
        'status' => 'draft',
        'is_paid' => false,
        'created_by' => $instructor->id,
    ]);

    actingAs($instructor);

    $response = get(route('dashboard.courses.index'));

    $response->assertStatus(200);
});

test('user without elearning course permission cannot access dashboard courses index', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');

    actingAs($learner);

    $response = get(route('dashboard.courses.index'));

    $response->assertStatus(403);
});

