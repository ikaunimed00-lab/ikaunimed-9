<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlumniDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = User::appearInDirectory()
            ->with([
                'educations',
                'enrollments' => function ($q) {
                    $q->whereIn('status', ['active', 'completed'])
                        ->latest('updated_at')
                        ->with('course');
                },
            ]);

        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%' . $search . '%');
        });

        $query->when($request->bidang_pekerjaan, function ($q, $bidang) {
            $q->where('bidang_pekerjaan', $bidang);
        });

        $query->when($request->status_pekerjaan, function ($q, $status) {
            $q->where('status_pekerjaan', $status);
        });

        $query->when($request->angkatan, function ($q, $angkatan) {
            $q->whereHas('educations', function ($sub) use ($angkatan) {
                $sub->where('admission_year', $angkatan);
            });
        });

        $query->latest('updated_at');

        $users = $query->paginate(12)->withQueryString();

        $alumni = $users->getCollection()->map(function (User $user) {
            $educationLevels = ['D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

            $primaryEducation = $user->educations
                ->filter(function ($edu) {
                    return $edu->major && $edu->admission_year;
                })
                ->sortBy(function ($edu) use ($educationLevels) {
                    $index = array_search($edu->level, $educationLevels);

                    return $index === false ? -1 : $index;
                })
                ->last();

            $enrollments = $user->enrollments->filter(function ($enrollment) {
                return $enrollment->course !== null;
            });

            $totalCourses = $enrollments->count();
            $completedCourses = $enrollments->where('status', 'completed')->count();

            $recentCourses = $enrollments
                ->take(3)
                ->map(function ($enrollment) {
                    return [
                        'title' => $enrollment->course->title,
                        'status' => $enrollment->status,
                        'progress_percentage' => $enrollment->progress_percentage,
                    ];
                })
                ->values();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'bidang_pekerjaan' => $user->bidang_pekerjaan,
                'posisi_saat_ini' => $user->posisi_saat_ini,
                'perusahaan' => $user->perusahaan,
                'kota_profesional' => $user->kota_profesional,
                'status_pekerjaan' => $user->status_pekerjaan,
                'profile_level' => $user->profile_level,
                'profile_completion_score' => $user->profile_completion_score,
                'primary_education' => $primaryEducation ? [
                    'level' => $primaryEducation->level,
                    'major' => $primaryEducation->major,
                    'admission_year' => $primaryEducation->admission_year,
                    'graduation_year' => $primaryEducation->graduation_year,
                ] : null,
                'elearning_activity' => [
                    'total_courses' => $totalCourses,
                    'completed_courses' => $completedCourses,
                    'recent_courses' => $recentCourses,
                ],
            ];
        });

        $users->setCollection($alumni);

        return Inertia::render('Alumni/Directory/Index', [
            'alumni' => $users,
            'filters' => $request->only([
                'search',
                'bidang_pekerjaan',
                'status_pekerjaan',
                'angkatan',
            ]),
        ]);
    }
}
