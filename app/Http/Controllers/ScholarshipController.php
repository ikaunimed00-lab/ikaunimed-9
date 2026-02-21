<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\ScholarshipApplicant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Scholarship::query()
            ->where('status', 'active')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            })
            ->when($request->degree, function ($q, $degree) {
                $normalized = match ($degree) {
                    'S1' => 's1',
                    'S2' => 's2',
                    'S3' => 's3',
                    'Semua Jenjang' => 'all',
                    default => null,
                };
                if ($normalized) {
                    $q->where('degree', $normalized);
                }
            })
            ->latest();

        return Inertia::render('Scholarship/Index', [
            'scholarships' => $query->paginate(9)->withQueryString(),
            'filters' => $request->only(['search', 'degree']),
        ]);
    }

    public function show(Scholarship $scholarship)
    {
        if ($scholarship->status !== 'active' && !auth()->user()?->can('cms.scholarship.publish')) {
            abort(404);
        }

        $related = Scholarship::where('status', 'active')
            ->where('id', '!=', $scholarship->id)
            ->where('degree', $scholarship->degree)
            ->limit(3)
            ->get();

        return Inertia::render('Scholarship/Show', [
            'scholarship' => $scholarship,
            'related' => $related,
        ]);
    }

    public function apply(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $this->middleware('auth');

        if ($scholarship->status !== 'active' && !auth()->user()?->can('cms.scholarship.publish')) {
            abort(404);
        }

        $validated = $request->validate([
            'essay' => ['required', 'string', 'min:30'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $userId = Auth::id();

        $exists = ScholarshipApplicant::where('scholarship_id', $scholarship->id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'form' => 'Anda sudah mendaftar pada beasiswa ini.',
            ]);
        }

        $cvPath = $request->file('cv')->store('scholarship_cvs', 'public');

        ScholarshipApplicant::create([
            'scholarship_id' => $scholarship->id,
            'user_id' => $userId,
            'essay' => $validated['essay'],
            'cv_path' => $cvPath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Lamaran berhasil dikirim. Status awal: pending.');
    }
}
