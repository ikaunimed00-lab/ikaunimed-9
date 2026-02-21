<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Partnership;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PartnershipDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Partnership::query()->latest();

        // Filter untuk non-editor (writer/subscriber)
        if (!$user->can('cms.job.publish')) {
            $query->where('user_id', $user->id);
        }

        return Inertia::render('Dashboard/Partnership/Index', [
            'partnerships' => $query->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Partnership/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'benefit_details' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('partnerships', 'public');
        }

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['slug'] = Str::slug($validated['name'] . '-' . Str::random(6));
        
        // Auto-approve untuk user dengan izin publish job (editor/admin), pending untuk lainnya
        $validated['status'] = $user->can('cms.job.publish') ? 'active' : 'pending';

        Partnership::create($validated);

        return redirect()->route('dashboard.partnerships.index')
            ->with('success', 'Kemitraan berhasil dibuat.');
    }

    public function edit(Partnership $partnership)
    {
        $this->authorize('update', $partnership);

        return Inertia::render('Dashboard/Partnership/Edit', [
            'partnership' => $partnership,
        ]);
    }

    public function update(Request $request, Partnership $partnership)
    {
        $this->authorize('update', $partnership);

        $rules = [
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'benefit_details' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ];

        // Hanya user dengan izin publish job yang boleh update status
        if (auth()->user()->can('cms.job.publish')) {
            $rules['status'] = 'required|in:active,pending,rejected,closed';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('partnerships', 'public');
        }

        $partnership->update($validated);

        return redirect()->route('dashboard.partnerships.index')
            ->with('success', 'Kemitraan berhasil diperbarui.');
    }

    public function destroy(Partnership $partnership)
    {
        $this->authorize('delete', $partnership);

        $partnership->delete();

        return redirect()->back()->with('success', 'Kemitraan berhasil dihapus.');
    }

    public function approve(Partnership $partnership)
    {
        $this->authorize('moderate', $partnership);

        $partnership->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Kemitraan berhasil disetujui.');
    }

    public function reject(Partnership $partnership)
    {
        $this->authorize('moderate', $partnership);

        $partnership->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Kemitraan berhasil ditolak.');
    }
}
