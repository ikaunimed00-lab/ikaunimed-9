<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->can('elearning.course.view')) {
            abort(403);
        }

        $baseQuery = Course::query();

        if (! $user->can('elearning.course.publish')) {
            $baseQuery->where('created_by', $user->id);
        }

        $status = $request->get('status');
        $category = $request->get('category');
        $search = $request->get('search');

        $coursesQuery = (clone $baseQuery)
            ->with('category')
            ->withCount([
                'enrollments as enrollments_count' => function ($q) {
                    $q->whereIn('status', ['active', 'completed']);
                },
            ])
            ->withAvg('enrollments as enrollments_avg_progress', 'progress_percentage')
            ->when($status, function ($q) use ($status) {
                if ($status === 'draft') {
                    $q->where('status', 'draft');
                } elseif ($status === 'published') {
                    $q->where('status', 'published');
                } elseif ($status === 'archived') {
                    $q->where('status', 'archived');
                }
            })
            ->when($category, function ($q) use ($category) {
                $q->whereHas('category', function ($sub) use ($category) {
                    $sub->where('slug', $category);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $courses = $coursesQuery->paginate(10)->withQueryString();

        $courseIds = (clone $baseQuery)->pluck('id');

        $totalCourses = $courseIds->count();

        $totalActiveParticipants = 0;
        $averageProgress = 0;

        if ($courseIds->isNotEmpty()) {
            $totalActiveParticipants = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->distinct('user_id')
                ->count('user_id');

            $averageProgress = (int) floor(
                Enrollment::whereIn('course_id', $courseIds)->avg('progress_percentage') ?? 0
            );
        }

        $categories = CourseCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Dashboard/Course/Index', [
            'courses' => $courses,
            'stats' => [
                'total_courses' => $totalCourses,
                'active_participants' => $totalActiveParticipants,
                'average_progress' => $averageProgress,
            ],
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
                'search' => $search,
            ],
            'lmsRoles' => [
                'instructor' => $user->hasRole('instructor'),
                'learner' => $user->hasRole('learner'),
                'lms_moderator' => $user->hasRole('lms_moderator'),
            ],
        ]);
    }

    public function moderator(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->can('elearning.course.view_any')) {
            abort(403);
        }

        $status = $request->get('status');
        $category = $request->get('category');
        $search = $request->get('search');

        $coursesQuery = Course::query()
            ->with('category')
            ->withCount([
                'enrollments as enrollments_count' => function ($q) {
                    $q->whereIn('status', ['active', 'completed']);
                },
            ])
            ->withAvg('enrollments as enrollments_avg_progress', 'progress_percentage')
            ->when($status, function ($q) use ($status) {
                if ($status === 'draft') {
                    $q->where('status', 'draft');
                } elseif ($status === 'published') {
                    $q->where('status', 'published');
                } elseif ($status === 'archived') {
                    $q->where('status', 'archived');
                }
            })
            ->when($category, function ($q) use ($category) {
                $q->whereHas('category', function ($sub) use ($category) {
                    $sub->where('slug', $category);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $courses = $coursesQuery->paginate(15)->withQueryString();

        $courseIds = Course::query()->pluck('id');

        $totalCourses = $courseIds->count();
        $totalActiveParticipants = 0;
        $averageProgress = 0;

        if ($courseIds->isNotEmpty()) {
            $totalActiveParticipants = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->distinct('user_id')
                ->count('user_id');

            $averageProgress = (int) floor(
                Enrollment::whereIn('course_id', $courseIds)->avg('progress_percentage') ?? 0
            );
        }

        $categories = CourseCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Dashboard/Moderator/Courses', [
            'courses' => $courses,
            'stats' => [
                'total_courses' => $totalCourses,
                'active_participants' => $totalActiveParticipants,
                'average_progress' => $averageProgress,
            ],
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
                'search' => $search,
            ],
            'lmsRoles' => [
                'instructor' => $user->hasRole('instructor'),
                'learner' => $user->hasRole('learner'),
                'lms_moderator' => $user->hasRole('lms_moderator'),
            ],
        ]);
    }

    public function participants(Request $request, Course $course)
    {
        $user = $request->user();

        if (! $user || ! $user->can('manage', $course)) {
            abort(403);
        }

        $status = $request->get('status');
        $progressFilter = $request->get('progress');
        $sort = $request->get('sort', 'started_at');
        $direction = $request->get('direction', 'desc');

        $enrollmentsQuery = Enrollment::query()
            ->with([
                'user' => function ($q) {
                    $q->select(
                        'id',
                        'name',
                        'email',
                        'bidang_pekerjaan',
                        'perusahaan',
                        'kota_profesional',
                        'status_pekerjaan',
                        'angkatan',
                        'public_profile',
                        'profile_level'
                    );
                },
            ])
            ->where('course_id', $course->id);

        if ($status === 'active') {
            $enrollmentsQuery->where('status', 'active');
        } elseif ($status === 'completed') {
            $enrollmentsQuery->where('status', 'completed');
        }

        if ($progressFilter === 'not_started') {
            $enrollmentsQuery->where(function ($q) {
                $q->whereNull('progress_percentage')->orWhere('progress_percentage', 0);
            });
        } elseif ($progressFilter === 'lt_25') {
            $enrollmentsQuery->where('progress_percentage', '>=', 1)->where('progress_percentage', '<', 25);
        } elseif ($progressFilter === '25_75') {
            $enrollmentsQuery->where('progress_percentage', '>=', 25)->where('progress_percentage', '<=', 75);
        } elseif ($progressFilter === 'gt_75') {
            $enrollmentsQuery->where('progress_percentage', '>', 75);
        }

        if ($sort === 'progress') {
            $enrollmentsQuery->orderBy('progress_percentage', $direction);
        } elseif ($sort === 'name') {
            $enrollmentsQuery->join('users', 'enrollments.user_id', '=', 'users.id')
                ->orderBy('users.name', $direction)
                ->select('enrollments.*');
        } else {
            $enrollmentsQuery->orderBy('started_at', $direction);
        }

        $enrollments = $enrollmentsQuery->paginate(15)->withQueryString();

        $statsQuery = Enrollment::where('course_id', $course->id);

        $totalParticipants = (clone $statsQuery)->count();
        $completedParticipants = (clone $statsQuery)->where('status', 'completed')->count();
        $averageProgress = (int) floor($statsQuery->avg('progress_percentage') ?? 0);

        $course->load('category');

        return Inertia::render('Dashboard/Course/Participants', [
            'course' => $course,
            'enrollments' => $enrollments,
            'stats' => [
                'total_participants' => $totalParticipants,
                'completed_participants' => $completedParticipants,
                'average_progress' => $averageProgress,
            ],
            'filters' => [
                'status' => $status,
                'progress' => $progressFilter,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'lmsRoles' => [
                'instructor' => $user->hasRole('instructor'),
                'learner' => $user->hasRole('learner'),
                'lms_moderator' => $user->hasRole('lms_moderator'),
            ],
        ]);
    }
}
