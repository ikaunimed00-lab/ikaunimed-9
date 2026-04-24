<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows only published courses on the index endpoint', function () {
    Course::create([
        'title' => 'Published Course',
        'slug' => 'published-course-endpoint',
        'status' => 'published',
        'is_paid' => false,
    ]);

    Course::create([
        'title' => 'Draft Course',
        'slug' => 'draft-course-endpoint',
        'status' => 'draft',
        'is_paid' => false,
    ]);

    $response = $this->get(route('courses.index'));

    $response->assertSuccessful();
    $response->assertSee('Published Course');
    $response->assertDontSee('Draft Course');
});

it('returns not found when guest opens unpublished course detail endpoint', function () {
    $course = Course::create([
        'title' => 'Hidden Course',
        'slug' => 'hidden-course-endpoint',
        'status' => 'draft',
        'is_paid' => false,
    ]);

    $response = $this->get(route('courses.show', $course->slug));

    $response->assertNotFound();
});

it('enrolls subscriber through course enroll endpoint', function () {
    $user = User::factory()->create([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1990-01-01',
        'alamat_lengkap' => 'Jl. Alumni No. 1',
    ]);
    $user->assignRole('subscriber');

    $course = Course::create([
        'title' => 'Free Course Enrollment',
        'slug' => 'free-course-enrollment-endpoint',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('courses.enroll', $course->slug));

    $response->assertRedirect(route('courses.show', $course->slug));
    $response->assertSessionHas('success', 'Anda berhasil mendaftar course ini.');

    $enrollment = Enrollment::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment)->not()->toBeNull();
    expect($enrollment->status)->toBe('active');
});
