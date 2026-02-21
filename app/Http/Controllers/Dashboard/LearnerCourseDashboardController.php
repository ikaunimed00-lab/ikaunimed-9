<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LearnerCourseDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->can('elearning.participant.enroll')) {
            abort(403);
        }

        $enrollmentsQuery = Enrollment::query()
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'slug', 'status', 'thumbnail', 'level');
            }])
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END")
            ->orderByDesc('started_at');

        $status = $request->get('status');

        if ($status === 'active') {
            $enrollmentsQuery->where('status', 'active');
        } elseif ($status === 'completed') {
            $enrollmentsQuery->where('status', 'completed');
        }

        $enrollments = $enrollmentsQuery->paginate(10)->withQueryString();

        $baseStatsQuery = Enrollment::query()->where('user_id', $user->id);

        $totalCourses = (clone $baseStatsQuery)->count();
        $completedCourses = (clone $baseStatsQuery)->where('status', 'completed')->count();
        $averageProgress = (int) floor($baseStatsQuery->avg('progress_percentage') ?? 0);

        return Inertia::render('Dashboard/Learner/Courses', [
            'enrollments' => $enrollments,
            'stats' => [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'average_progress' => $averageProgress,
            ],
            'filters' => [
                'status' => $status,
            ],
            'lmsRoles' => [
                'instructor' => $user->hasRole('instructor'),
                'learner' => $user->hasRole('learner'),
                'lms_moderator' => $user->hasRole('lms_moderator'),
            ],
        ]);
    }

    public function cancel(Request $request, Enrollment $enrollment)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $this->authorize('cancel', $enrollment);

        if ($enrollment->status === 'active') {
            $enrollment->status = 'cancelled';
            $enrollment->save();
        }

        return redirect()
            ->route('dashboard.elearning.learner.courses.index')
            ->with('success', 'Enrollment berhasil dibatalkan.');
    }
}
