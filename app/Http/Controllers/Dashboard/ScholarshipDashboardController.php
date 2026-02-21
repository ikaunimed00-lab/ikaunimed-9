<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ScholarshipDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Scholarship::query()->latest();

        // Filter for non-editor (writer/subscriber)
        if (!$user->can('cms.scholarship.publish')) {
            $query->where('user_id', $user->id);
        }

        return Inertia::render('Dashboard/Scholarship/Index', [
            'scholarships' => $query->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Scholarship/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'degree' => 'required|string',
            'description' => 'required|string',
            'coverage_type' => 'required|string',
            'deadline' => 'nullable|date',
            'link' => 'nullable|url',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('scholarships', 'public');
        }

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['slug'] = Str::slug($validated['title'] . '-' . Str::random(6));
        
        $validated['status'] = 'pending';

        Scholarship::create($validated);

        return redirect()->route('dashboard.scholarships.index')
            ->with('success', 'Beasiswa berhasil dibuat.');
    }

    public function edit(Scholarship $scholarship)
    {
        $this->authorize('update', $scholarship);

        return Inertia::render('Dashboard/Scholarship/Edit', [
            'scholarship' => $scholarship,
        ]);
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $this->authorize('update', $scholarship);

        $rules = [
            'title' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'degree' => 'required|string',
            'description' => 'required|string',
            'coverage_type' => 'required|string',
            'deadline' => 'nullable|date',
            'link' => 'nullable|url',
            'image' => 'nullable|image|max:2048',
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('scholarships', 'public');
        }

        $scholarship->update($validated);

        return redirect()->route('dashboard.scholarships.index')
            ->with('success', 'Beasiswa berhasil diperbarui.');
    }

    public function destroy(Scholarship $scholarship)
    {
        $this->authorize('delete', $scholarship);

        $scholarship->delete();

        return redirect()->back()->with('success', 'Beasiswa berhasil dihapus.');
    }

    public function approve(Scholarship $scholarship)
    {
        abort(404);
    }

    public function reject(Scholarship $scholarship)
    {
        abort(404);
    }
}
