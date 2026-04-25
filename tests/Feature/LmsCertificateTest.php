<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\Certificate;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('certificate is automatically issued when course progress reaches 100 percent', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->forceFill([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => now()->subYears(25)->toDateString(),
        'alamat_lengkap' => 'Alamat Lengkap',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2010',
        's1_tahun_tamat' => '2014',
    ])->save();

    $course = Course::create([
        'title' => 'Certificate Course',
        'slug' => 'certificate-course',
        'status' => 'published',
        'is_paid' => false,
        'created_by' => $user->id,
    ]);

    $lessonA = Lesson::create([
        'course_id' => $course->id,
        'title' => 'Lesson A',
        'slug' => 'lesson-a',
        'type' => 'video',
        'order' => 1,
    ]);

    $lessonB = Lesson::create([
        'course_id' => $course->id,
        'title' => 'Lesson B',
        'slug' => 'lesson-b',
        'type' => 'video',
        'order' => 2,
    ]);

    Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
        'started_at' => now(),
        'progress_percentage' => 0,
    ]);

    actingAs($user);

    post(route('courses.lessons.complete', [$course->slug, $lessonA->id]))
        ->assertRedirect();

    post(route('courses.lessons.complete', [$course->slug, $lessonB->id]))
        ->assertRedirect();

    $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->progress_percentage)->toBe(100);

    $certificate = Certificate::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($certificate)->not->toBeNull();
    expect($certificate->certificate_number)->not->toBeEmpty();
    expect($certificate->issued_at)->not->toBeNull();
});
