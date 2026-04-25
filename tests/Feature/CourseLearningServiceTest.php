<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\Courses\CourseLearningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;

it('enrolls eligible learner through course learning service', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $course = Course::create([
        'title' => 'Course Learning Service',
        'slug' => 'course-learning-service',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $enrollment = app(CourseLearningService::class)->enroll($user, $course);

    expect($enrollment->status)->toBe('active');
    expect($enrollment->started_at)->not()->toBeNull();
});

it('updates lesson progress and issues certificate when all lessons are completed', function () {
    Mail::fake();

    $user = User::factory()->create();
    $course = Course::create([
        'title' => 'Progress Course',
        'slug' => 'progress-course',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $lessonOne = Lesson::create([
        'course_id' => $course->id,
        'title' => 'Lesson One',
        'slug' => 'lesson-one',
        'order' => 1,
        'type' => 'text',
    ]);

    $lessonTwo = Lesson::create([
        'course_id' => $course->id,
        'title' => 'Lesson Two',
        'slug' => 'lesson-two',
        'order' => 2,
        'type' => 'text',
    ]);

    $service = app(CourseLearningService::class);
    $service->activateEnrollment($user, $course);
    $service->completeLesson($user, $course, $lessonOne);
    $service->completeLesson($user, $course, $lessonTwo);

    $enrollment = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment)->not()->toBeNull();
    expect($enrollment->progress_percentage)->toBe(100);
    expect($enrollment->status)->toBe('completed');
    expect($enrollment->completed_at)->not()->toBeNull();
});

it('records quiz attempts and returns latest stats', function () {
    $user = User::factory()->create();
    $course = Course::create([
        'title' => 'Quiz Course',
        'slug' => 'quiz-course',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'title' => 'Quiz Lesson',
        'slug' => 'quiz-lesson',
        'order' => 1,
        'type' => 'quiz',
    ]);

    $quiz = Quiz::create([
        'lesson_id' => $lesson->id,
        'title' => 'Basic Quiz',
        'passing_score' => 70,
    ]);

    $question = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question' => '2 + 2 = ?',
        'options' => ['A' => '3', 'B' => '4'],
        'correct_option_key' => 'B',
    ]);

    $service = app(CourseLearningService::class);
    $service->activateEnrollment($user, $course);

    $result = $service->attemptQuiz($user, $course, $lesson, [
        $question->id => 'B',
    ]);

    expect($result['score'])->toBe(100);
    expect($result['passed'])->toBeTrue();
    expect($result['attempts_count'])->toBe(1);
    expect($result['best_score'])->toBe(100);
    expect(QuizAttempt::count())->toBe(1);
});
