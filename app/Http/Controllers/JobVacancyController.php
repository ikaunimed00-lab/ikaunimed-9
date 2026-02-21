<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobVacancyController extends Controller
{
    public function index(Request $request)
    {
        $query = JobVacancy::query()
            ->where('status', 'active')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->latest();

        $alumniBaseQuery = User::appearInDirectory();

        $alumniStats = [
            'total' => (clone $alumniBaseQuery)->count(),
            'open_to_work' => (clone $alumniBaseQuery)
                ->where('status_pekerjaan', 'mencari_kerja')
                ->count(),
        ];

        return Inertia::render('JobVacancy/Index', [
            'vacancies' => $query->paginate(9)->withQueryString(),
            'filters' => $request->only(['search', 'type']),
            'alumniStats' => $alumniStats,
        ]);
    }

    public function show(JobVacancy $vacancy)
    {
        if ($vacancy->status !== 'active' && auth()->id() !== $vacancy->user_id && !auth()->user()?->isAdminOrEditor()) {
            abort(404);
        }

        $related = JobVacancy::where('status', 'active')
            ->where('id', '!=', $vacancy->id)
            ->where('type', $vacancy->type)
            ->limit(3)
            ->get();

        $user = auth()->user();

        $interest = [
            'is_interested' => false,
            'total' => $vacancy->interestedUsers()->count(),
        ];

        if ($user && $user->canAppearInDirectory()) {
            $interest['is_interested'] = $vacancy->interestedUsers()
                ->where('user_id', $user->id)
                ->exists();
        }

        return Inertia::render('JobVacancy/Show', [
            'vacancy' => $vacancy,
            'related' => $related,
            'interest' => $interest,
        ]);
    }

    public function toggleInterest(Request $request, JobVacancy $vacancy)
    {
        $user = $request->user();

        if (! $user || ! $user->canAppearInDirectory()) {
            abort(403);
        }

        $alreadyInterested = $vacancy->interestedUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyInterested) {
            $vacancy->interestedUsers()->detach($user->id);
        } else {
            $vacancy->interestedUsers()->attach($user->id);
        }

        return redirect()->back();
    }
}
