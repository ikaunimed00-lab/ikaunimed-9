<?php

namespace App\Services\Courses;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseLearningService
{
    public function enroll(User $user, Course $course): Enrollment
    {
        if (! $user->can('enroll', $course)) {
            throw new AuthorizationException();
        }

        return $this->activateEnrollment($user, $course);
    }

    public function activateEnrollment(User $user, Course $course): Enrollment
    {
        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'status' => 'active',
                'started_at' => now(),
            ]
        );

        if (! $enrollment->started_at) {
            $enrollment->started_at = now();
            $enrollment->status = 'active';
            $enrollment->save();
        }

        return $enrollment;
    }

    public function completeLesson(User $user, Course $course, Lesson $lesson): void
    {
        $this->assertLessonBelongsToCourse($course, $lesson);
        $enrollment = $this->assertEnrolled($user, $course);

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]
        );

        if ($progress->is_completed) {
            return;
        }

        $progress->is_completed = true;
        $progress->completed_at = now();
        $progress->save();

        $lessonIds = $course->lessons()->pluck('id');

        if ($lessonIds->isEmpty()) {
            return;
        }

        $completedCount = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->count();

        $totalLessons = $lessonIds->count();
        if ($totalLessons <= 0) {
            return;
        }

        $percentage = (int) floor(($completedCount / $totalLessons) * 100);
        $enrollment->progress_percentage = $percentage;

        $wasCompleted = (bool) $enrollment->completed_at;
        if ($percentage === 100 && ! $enrollment->completed_at) {
            $enrollment->completed_at = now();
            $enrollment->status = 'completed';
        }

        $enrollment->save();

        if ($percentage === 100 && ! $wasCompleted) {
            $this->issueCertificate($user, $course);
        }
    }

    /**
     * @param  array<int|string, string>  $answers
     * @return array{score: int, passed: bool, attempts_count: int, best_score: int}
     */
    public function attemptQuiz(User $user, Course $course, Lesson $lesson, array $answers): array
    {
        $this->assertLessonBelongsToCourse($course, $lesson);
        $this->assertEnrolled($user, $course);

        if ($lesson->type !== 'quiz' || ! $lesson->quiz) {
            throw new BadRequestHttpException();
        }

        $quiz = $lesson->quiz()->with('questions')->first();
        if (! $quiz) {
            throw new NotFoundHttpException();
        }

        $correctCount = 0;
        $questions = $quiz->questions;

        foreach ($questions as $question) {
            $selected = $answers[$question->id] ?? null;

            if ($selected && $selected === $question->correct_option_key) {
                $correctCount++;
            }
        }

        $totalQuestions = $questions->count();
        $score = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;
        $passed = $score >= $quiz->passing_score;

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'is_passed' => $passed,
            'answers' => $answers,
        ]);

        $attemptQuery = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id);

        return [
            'score' => $score,
            'passed' => $passed,
            'attempts_count' => $attemptQuery->count(),
            'best_score' => (int) $attemptQuery->max('score'),
        ];
    }

    private function assertLessonBelongsToCourse(Course $course, Lesson $lesson): void
    {
        if ($lesson->course_id !== $course->id) {
            throw new NotFoundHttpException();
        }
    }

    private function assertEnrolled(User $user, Course $course): Enrollment
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            throw new HttpException(403);
        }

        return $enrollment;
    }

    private function issueCertificate(User $user, Course $course): void
    {
        $certificate = Certificate::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'certificate_number' => 'CERT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'issued_at' => now(),
            ]
        );

        if (! $certificate->wasRecentlyCreated || ! $user->email) {
            return;
        }

        $subject = 'Sertifikat Kursus LMS Anda Telah Terbit';
        $body = '<p>Halo ' . e($user->name) . ',</p>'
            . '<p>Selamat! Anda telah menyelesaikan kursus <strong>' . e($course->title) . '</strong>.</p>'
            . '<p>Sertifikat Anda dengan nomor <strong>' . e($certificate->certificate_number) . '</strong> sudah diterbitkan dan dapat diunduh melalui dashboard LMS pada menu Sertifikat Saya.</p>'
            . '<p>Terima kasih telah aktif belajar di portal alumni IKA UNIMED.</p>'
            . '<p>Salam hangat,<br>' . e(config('app.name')) . '</p>';

        Mail::html($body, function ($message) use ($user, $subject): void {
            $message->to($user->email, $user->name)->subject($subject);
        });
    }
}
