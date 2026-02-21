<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
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

        return Inertia::render('Course/Show', [
            'course' => $course,
            'related' => $related,
            'enrollment' => $enrollment,
            'lessonProgress' => $lessonProgress,
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('enroll', $course);

        $user = $request->user();

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

        return redirect()
            ->route('courses.show', $course->slug)
            ->with('success', 'Anda berhasil mendaftar course ini.');
    }

    public function completeLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            abort(403);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]
        );

        if (! $progress->is_completed) {
            $progress->is_completed = true;
            $progress->completed_at = now();
            $progress->save();

            $lessonIds = $course->lessons()->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                $completedCount = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('is_completed', true)
                    ->count();

                $totalLessons = $lessonIds->count();

                if ($totalLessons > 0) {
                    $percentage = (int) floor(($completedCount / $totalLessons) * 100);

                    $enrollment->progress_percentage = $percentage;

                    if ($percentage === 100 && ! $enrollment->completed_at) {
                        $enrollment->completed_at = now();
                        $enrollment->status = 'completed';
                    }

                    $enrollment->save();
                }
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Progress belajar berhasil diperbarui.');
    }
}
