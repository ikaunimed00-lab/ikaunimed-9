<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Product;
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
    ) {}

    public function index(Request $request): Response
    {
        $query = Course::query()
            ->with('category')
            ->where('status', 'published')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', '%'.$search.'%');
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

        $user = Auth::user();
        $isPremiumMember = (bool) ($user?->hasRole('premium_member'));
        $premiumMembershipProduct = Product::query()
            ->where('is_published', true)
            ->where('membership_role', 'premium_member')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first(['id', 'slug', 'name', 'price']);

        $courses = $query->paginate(9)->withQueryString();
        $courseIds = $courses->getCollection()->pluck('id');
        $enrolledCourseIds = collect();

        if ($user && $courseIds->isNotEmpty() && $user->can('portal.enrollment.view_own')) {
            $enrolledCourseIds = Enrollment::query()
                ->where('user_id', $user->id)
                ->whereIn('course_id', $courseIds)
                ->pluck('course_id');
        }

        $courses->getCollection()->transform(function (Course $course) use ($user, $isPremiumMember, $enrolledCourseIds) {
            $requiresPremium = (bool) ($course->requires_premium ?? false);
            $isEnrolled = $enrolledCourseIds->contains($course->id);
            $canEnroll = (bool) ($user && $user->can('enroll', $course));
            $isLocked = $requiresPremium && ! $isPremiumMember;

            $reason = null;
            if (! $canEnroll) {
                if ($course->status !== 'published') {
                    $reason = 'course_unpublished';
                } elseif ($course->is_paid) {
                    $reason = 'course_paid';
                } elseif ($requiresPremium && ! $isPremiumMember) {
                    $reason = 'premium_required';
                } elseif (! $user) {
                    $reason = 'login_required';
                } elseif ($isEnrolled) {
                    $reason = 'already_enrolled';
                } else {
                    $reason = 'not_eligible';
                }
            }

            $course->setAttribute('enrollment_eligibility', [
                'is_enrolled' => $isEnrolled,
                'can_enroll' => $canEnroll,
                'requires_premium' => $requiresPremium,
                'is_premium_member' => $isPremiumMember,
                'is_locked' => $isLocked,
                'reason' => $reason,
            ]);

            return $course;
        });

        return Inertia::render('Course/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'level', 'price_type']),
            'premiumMembershipProduct' => $premiumMembershipProduct,
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
            ->with('category')
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

        $requiresPremium = (bool) ($course->requires_premium ?? false);
        $isPremiumMember = (bool) ($user?->hasRole('premium_member'));
        $canEnroll = (bool) ($user && $user->can('enroll', $course));
        $relatedCourseIds = $related->pluck('id');
        $enrolledRelatedCourseIds = collect();

        if ($user && $relatedCourseIds->isNotEmpty() && $user->can('portal.enrollment.view_own')) {
            $enrolledRelatedCourseIds = Enrollment::query()
                ->where('user_id', $user->id)
                ->whereIn('course_id', $relatedCourseIds)
                ->pluck('course_id');
        }

        $related = $related->map(function (Course $relatedCourse) use ($user, $isPremiumMember, $enrolledRelatedCourseIds) {
            $relatedRequiresPremium = (bool) ($relatedCourse->requires_premium ?? false);
            $isRelatedEnrolled = $enrolledRelatedCourseIds->contains($relatedCourse->id);
            $canRelatedEnroll = (bool) ($user && $user->can('enroll', $relatedCourse));
            $isRelatedLocked = $relatedRequiresPremium && ! $isPremiumMember;

            $relatedReason = null;
            if (! $canRelatedEnroll) {
                if ($relatedCourse->status !== 'published') {
                    $relatedReason = 'course_unpublished';
                } elseif ($relatedCourse->is_paid) {
                    $relatedReason = 'course_paid';
                } elseif ($relatedRequiresPremium && ! $isPremiumMember) {
                    $relatedReason = 'premium_required';
                } elseif (! $user) {
                    $relatedReason = 'login_required';
                } elseif ($isRelatedEnrolled) {
                    $relatedReason = 'already_enrolled';
                } else {
                    $relatedReason = 'not_eligible';
                }
            }

            $relatedCourse->setAttribute('enrollment_eligibility', [
                'is_enrolled' => $isRelatedEnrolled,
                'can_enroll' => $canRelatedEnroll,
                'requires_premium' => $relatedRequiresPremium,
                'is_premium_member' => $isPremiumMember,
                'is_locked' => $isRelatedLocked,
                'reason' => $relatedReason,
            ]);

            return $relatedCourse;
        })->values();

        $premiumMembershipProduct = Product::query()
            ->where('is_published', true)
            ->where('membership_role', 'premium_member')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first(['id', 'slug', 'name', 'price']);

        $enrollmentEligibilityReason = null;

        if (! $canEnroll) {
            if ($course->status !== 'published') {
                $enrollmentEligibilityReason = 'course_unpublished';
            } elseif ($course->is_paid) {
                $enrollmentEligibilityReason = 'course_paid';
            } elseif ($requiresPremium && ! $isPremiumMember) {
                $enrollmentEligibilityReason = 'premium_required';
            } elseif (! $user) {
                $enrollmentEligibilityReason = 'login_required';
            } else {
                $enrollmentEligibilityReason = 'not_eligible';
            }
        }

        return Inertia::render('Course/Show', [
            'course' => $course,
            'related' => $related,
            'enrollment' => $enrollment,
            'lessonProgress' => $lessonProgress,
            'nextLessonId' => $nextLessonId,
            'enrollmentEligibility' => [
                'can_enroll' => $canEnroll,
                'reason' => $enrollmentEligibilityReason,
                'requires_premium' => $requiresPremium,
                'is_premium_member' => $isPremiumMember,
            ],
            'premiumMembershipProduct' => $premiumMembershipProduct,
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
