<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

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
        $totalRevenue = 0;

        if ($courseIds->isNotEmpty()) {
            $totalActiveParticipants = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->distinct('user_id')
                ->count('user_id');

            $averageProgress = (int) floor(
                Enrollment::whereIn('course_id', $courseIds)->avg('progress_percentage') ?? 0
            );

            $revenueQuery = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('products.course_id', $courseIds)
                ->where('products.type', 'digital')
                ->where('orders.status', 'paid');

            if ($dateFrom) {
                $revenueQuery->whereDate('orders.paid_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $revenueQuery->whereDate('orders.paid_at', '<=', $dateTo);
            }

            $revenueByCourse = $revenueQuery
                ->groupBy('products.course_id')
                ->selectRaw('products.course_id as course_id, SUM(order_items.total) as revenue')
                ->pluck('revenue', 'course_id');

            $totalRevenue = (int) $revenueByCourse->sum();

            $courses->getCollection()->transform(function ($course) use ($revenueByCourse) {
                $course->revenue_total = (int) ($revenueByCourse[$course->id] ?? 0);

                return $course;
            });
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
                'total_revenue' => $totalRevenue,
            ],
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
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
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

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

        $stats = Cache::remember('lms.dashboard.moderator.stats', 60 * 15, function () use ($courseIds, $dateFrom, $dateTo) {
            $totalCourses = $courseIds->count();
            $totalActiveParticipants = 0;
            $averageProgress = 0;
            $totalRevenue = 0;
            $revenueByCourse = [];

            if ($courseIds->isNotEmpty()) {
                $totalActiveParticipants = Enrollment::whereIn('course_id', $courseIds)
                    ->where('status', 'active')
                    ->distinct('user_id')
                    ->count('user_id');

                $averageProgress = (int) floor(
                    Enrollment::whereIn('course_id', $courseIds)->avg('progress_percentage') ?? 0
                );

                $revenueQuery = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->whereIn('products.course_id', $courseIds)
                    ->where('products.type', 'digital')
                    ->where('orders.status', 'paid');

                if ($dateFrom) {
                    $revenueQuery->whereDate('orders.paid_at', '>=', $dateFrom);
                }

                if ($dateTo) {
                    $revenueQuery->whereDate('orders.paid_at', '<=', $dateTo);
                }

                $revenueByCourseCollection = $revenueQuery
                    ->groupBy('products.course_id')
                    ->selectRaw('products.course_id as course_id, SUM(order_items.total) as revenue')
                    ->pluck('revenue', 'course_id');

                $totalRevenue = (int) $revenueByCourseCollection->sum();
                $revenueByCourse = $revenueByCourseCollection
                    ->map(function ($value) {
                        return (int) $value;
                    })
                    ->toArray();
            }

            $sevenDaysAgo = now()->subDays(7);

            $webhookTotalLast7Days = PaymentLog::where('created_at', '>=', $sevenDaysAgo)->count();

            $webhookErrorLast7Days = PaymentLog::where('created_at', '>=', $sevenDaysAgo)
                ->where('status_code', '>=', 400)
                ->count();

            $enrollmentNewLast7Days = Enrollment::where('created_at', '>=', $sevenDaysAgo)->count();

            $enrollmentCompletedLast7Days = Enrollment::whereNotNull('completed_at')
                ->where('completed_at', '>=', $sevenDaysAgo)
                ->count();

            $webhookErrorRateLast7Days = $webhookTotalLast7Days > 0
                ? (int) floor(($webhookErrorLast7Days / $webhookTotalLast7Days) * 100)
                : 0;

            $enrollmentCompletionRateLast7Days = $enrollmentNewLast7Days > 0
                ? (int) floor(($enrollmentCompletedLast7Days / $enrollmentNewLast7Days) * 100)
                : 0;

            $health = [
                'webhook_total_last_7_days' => $webhookTotalLast7Days,
                'webhook_error_last_7_days' => $webhookErrorLast7Days,
                'webhook_error_rate_last_7_days' => $webhookErrorRateLast7Days,
                'enrollment_new_last_7_days' => $enrollmentNewLast7Days,
                'enrollment_completed_last_7_days' => $enrollmentCompletedLast7Days,
                'enrollment_completion_rate_last_7_days' => $enrollmentCompletionRateLast7Days,
            ];

            return [
                'total_courses' => $totalCourses,
                'active_participants' => $totalActiveParticipants,
                'average_progress' => $averageProgress,
                'total_revenue' => $totalRevenue,
                'revenue_by_course' => $revenueByCourse,
                'health' => $health,
            ];
        });

        $totalCourses = $stats['total_courses'];
        $totalActiveParticipants = $stats['active_participants'];
        $averageProgress = $stats['average_progress'];
        $totalRevenue = $stats['total_revenue'];
        $revenueByCourse = $stats['revenue_by_course'];
        $healthStats = $stats['health'];

        if (! empty($revenueByCourse)) {
            $courses->getCollection()->transform(function ($course) use ($revenueByCourse) {
                $course->revenue_total = (int) ($revenueByCourse[$course->id] ?? 0);

                return $course;
            });
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
                'total_revenue' => $totalRevenue,
            ],
            'healthStats' => $healthStats,
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
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
