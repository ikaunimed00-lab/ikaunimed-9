<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\Courses\CourseLearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseLearningService $courseLearningService
    ) {
    }

    public function index(Request $request): Response
    {
        $query = Course::query()
            ->with('category')
            ->where('status', 'published')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', '%' . $search . '%');
            })
            ->when($request->category, function ($q, $category) {
                $q->whereHas('category', function ($sub) use ($category) {
                    $sub->where('slug', $category);
                });
            })
            ->when($request->level, function ($q, $level) {
                $q->where('level', $level);
            })
            ->when($request->price_type, function ($q, $priceType) {
                if ($priceType === 'free') {
                    $q->where('is_paid', false);
                } elseif ($priceType === 'paid') {
                    $q->where('is_paid', true);
                }
            })
            ->latest('published_at');

        $categories = CourseCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Course/Index', [
            'courses' => $query->paginate(9)->withQueryString(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'level', 'price_type']),
        ]);
    }

    public function show(Course $course): Response
    {
        $user = Auth::user();

        if ($course->status !== 'published') {
            if (! $user || ! $user->can('manage', $course)) {
                abort(404);
            }
        }

        $course->load([
            'category',
            'creator:id,name',
            'modules.lessons',
            'lessons',
            'product',
        ]);

        $related = Course::query()
            ->where('status', 'published')
            ->where('id', '!=', $course->id)
            ->when($course->category_id, function ($q) use ($course) {
                $q->where('category_id', $course->category_id);
            })
            ->limit(3)
            ->get();

        $enrollment = null;
        $lessonProgress = [];

        if ($user && $user->can('portal.enrollment.view_own')) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $lessonIds = $course->lessons->pluck('id');

                if ($lessonIds->isNotEmpty()) {
                    $lessonProgress = LessonProgress::where('user_id', $user->id)
                        ->whereIn('lesson_id', $lessonIds)
                        ->pluck('is_completed', 'lesson_id');
                }
            }
        }

        $nextLessonId = null;

        if ($enrollment && $course->lessons->isNotEmpty()) {
            $orderedLessons = $course->lessons->sortBy('order')->values();

            foreach ($orderedLessons as $lesson) {
                if (empty($lessonProgress[$lesson->id])) {
                    $nextLessonId = $lesson->id;
                    break;
                }
            }
        }

        return Inertia::render('Course/Show', [
            'course' => $course,
            'related' => $related,
            'enrollment' => $enrollment,
            'lessonProgress' => $lessonProgress,
            'nextLessonId' => $nextLessonId,
        ]);
    }

    public function showLesson(Request $request, Course $course, Lesson $lesson): Response
    {
        $user = $request->user();

        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        if (! $user) {
            abort(403);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            abort(403);
        }

        $lesson->load([
            'quiz.questions',
        ]);

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $quizStats = null;

        if ($lesson->quiz) {
            $attemptQuery = $lesson->quiz->attempts()
                ->where('user_id', $user->id);

            $attemptsCount = $attemptQuery->count();
            $bestScore = $attemptsCount > 0 ? (int) $attemptQuery->max('score') : null;

            $attempts = $attemptQuery
                ->latest()
                ->take(10)
                ->get(['id', 'score', 'is_passed', 'created_at']);

            $quizStats = [
                'attempts_count' => $attemptsCount,
                'best_score' => $bestScore,
                'attempts' => $attempts,
            ];
        }

        $nextLessonId = null;

        $lessonIdsOrdered = $course->lessons()
            ->orderBy('order')
            ->pluck('id')
            ->all();

        if (! empty($lessonIdsOrdered)) {
            $currentIndex = array_search($lesson->id, $lessonIdsOrdered, true);

            if ($currentIndex !== false) {
                $nextIndex = $currentIndex + 1;

                if (isset($lessonIdsOrdered[$nextIndex])) {
                    $nextLessonId = $lessonIdsOrdered[$nextIndex];
                }
            }
        }

        return Inertia::render('Course/LessonShow', [
            'course' => $course->only(['id', 'title', 'slug']),
            'lesson' => $lesson,
            'quiz' => $lesson->quiz,
            'lessonCompleted' => $progress ? (bool) $progress->is_completed : false,
            'quizStats' => $quizStats,
            'nextLessonId' => $nextLessonId,
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $this->courseLearningService->enroll($user, $course);

        return redirect()
            ->route('courses.show', $course->slug)
            ->with('success', 'Anda berhasil mendaftar course ini.');
    }

    public function completeLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        $this->courseLearningService->completeLesson($user, $course, $lesson);

        return redirect()
            ->back()
            ->with('success', 'Progress belajar berhasil diperbarui.');
    }

    public function attemptQuiz(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $result = $this->courseLearningService->attemptQuiz($user, $course, $lesson, $data['answers']);

        return response()->json($result);
    }
}
