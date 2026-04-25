<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
        $totalCertificates = Certificate::where('user_id', $user->id)->count();

        return Inertia::render('Dashboard/Learner/Courses', [
            'enrollments' => $enrollments,
            'stats' => [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'average_progress' => $averageProgress,
                'total_certificates' => $totalCertificates,
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

    public function certificates(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->can('elearning.participant.enroll')) {
            abort(403);
        }

        $certificates = Certificate::query()
            ->with(['course:id,title,slug'])
            ->where('user_id', $user->id)
            ->latest('issued_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Learner/Certificates', [
            'certificates' => $certificates,
            'lmsRoles' => [
                'instructor' => $user->hasRole('instructor'),
                'learner' => $user->hasRole('learner'),
                'lms_moderator' => $user->hasRole('lms_moderator'),
            ],
        ]);
    }

    public function downloadCertificate(Request $request, Certificate $certificate)
    {
        $user = $request->user();

        if (! $user || $certificate->user_id !== $user->id) {
            abort(403);
        }

        $certificate->loadMissing('user', 'course');

        $relativePath = null;

        if ($certificate->file_path) {
            $publicPrefix = '/storage/';
            if (str_starts_with($certificate->file_path, $publicPrefix)) {
                $relativePath = substr($certificate->file_path, strlen($publicPrefix));
            }
        }

        if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
            $filename = 'certificate-'.$certificate->id.'-'.$certificate->certificate_number.'.html';
            $relativePath = 'certificates/'.$filename;

            $html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Sertifikat '
                .$certificate->certificate_number
                .'</title><style>body{font-family:system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", sans-serif;background:#f3f4f6;padding:40px;}'
                .'.wrapper{max-width:800px;margin:0 auto;background:white;border:1px solid #e5e7eb;border-radius:16px;padding:40px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);}'
                .'.title{font-size:28px;font-weight:700;text-align:center;margin-bottom:24px;color:#111827;}'
                .'.meta{font-size:14px;color:#6b7280;margin-bottom:24px;text-align:center;}'
                .'.field{margin-bottom:8px;font-size:14px;color:#374151;}'
                .'.label{font-weight:600;color:#6b7280;margin-right:4px;}'
                .'.footer{margin-top:32px;font-size:12px;color:#9ca3af;text-align:center;}</style></head><body>'
                .'<div class="wrapper">'
                .'<div class="title">Sertifikat Penyelesaian Kursus</div>'
                .'<div class="meta">Nomor Sertifikat: '.$certificate->certificate_number.'</div>'
                .'<div class="field"><span class="label">Nama:</span><span>'
                .e($certificate->user?->name ?? '')
                .'</span></div>'
                .'<div class="field"><span class="label">Kursus:</span><span>'
                .e($certificate->course?->title ?? '')
                .'</span></div>'
                .'<div class="field"><span class="label">Tanggal Terbit:</span><span>'
                .$certificate->issued_at->format('d M Y')
                .'</span></div>'
                .'<div class="footer">Dokumen ini dihasilkan otomatis oleh sistem LMS IKA UNIMED.</div>'
                .'</div></body></html>';

            Storage::disk('public')->put($relativePath, $html);

            $certificate->file_path = Storage::url($relativePath);
            $certificate->save();
        }

        $downloadName = 'sertifikat-'.$certificate->certificate_number.'.html';

        return Storage::disk('public')->download($relativePath, $downloadName);
    }
}
