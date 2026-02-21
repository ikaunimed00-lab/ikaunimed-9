<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Organization::query();

        if (! $user->isCentralAdmin()) {
            if ($user->organization_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('id', $user->organization_id)
                      ->orWhere('parent_id', $user->organization_id);
                });
            } else {
                abort(403, 'Unauthorized access');
            }
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $organizations = $query->with('parent')
            ->orderBy('type', 'asc') // pp, dpw, dpc
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Organization/Index', [
            'organizations' => $organizations,
            'filters' => $request->only(['search', 'type']),
            'auth_user_scope' => [
                'is_central_admin' => $user->isCentralAdmin(),
                'organization_id' => $user->organization_id,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Access Control
        if (!$user->isCentralAdmin() && $user->organization->type === 'dpc') {
             abort(403, 'DPC admins cannot create sub-organizations.');
        }

        $query = Organization::whereIn('type', ['pp', 'dpw']);
        
        if (!$user->isCentralAdmin()) {
            // If DPW Admin, parent MUST be their own organization
            $query->where('id', $user->organization_id);
        }

        $parents = $query->get();

        return Inertia::render('Dashboard/Admin/Organization/Create', [
            'parents' => $parents,
            'default_parent_id' => !$user->isCentralAdmin() ? $user->organization_id : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Basic validation first
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pp,dpw,dpc',
            'parent_id' => 'nullable|exists:organizations,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Enforcement of Scope
        if (!$user->isCentralAdmin()) {
             if ($user->organization->type === 'dpc') {
                 abort(403, 'Unauthorized action.');
             }
             // DPW Admin creating DPC
             if ($validated['type'] !== 'dpc') {
                 return back()->withErrors(['type' => 'Anda hanya dapat membuat organisasi DPC.']);
             }
             // Parent MUST be their own organization
             if ($validated['parent_id'] != $user->organization_id) {
                 return back()->withErrors(['parent_id' => 'Induk organisasi harus sesuai dengan wilayah Anda.']);
             }
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Pastikan slug unik
        $count = Organization::where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        Organization::create($validated);

        return redirect()->route('filament.admin.resources.organizations.index')
            ->with('success', 'Organisasi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        $user = auth()->user();

        // Scope Check
        if (!$user->isCentralAdmin()) {
            $isOwn = $organization->id === $user->organization_id;
            $isChild = $organization->parent_id === $user->organization_id;
            
            if (!$isOwn && !$isChild) {
                abort(403, 'Unauthorized access.');
            }
        }

        $query = Organization::whereIn('type', ['pp', 'dpw'])
            ->where('id', '!=', $organization->id); // Hindari self-parenting
        
        if (!$user->isCentralAdmin()) {
             // If DPW Admin, they can only assign themselves as parent to their children
             // Or if editing themselves, they might not be able to change parent?
             // Usually parent is fixed for non-super admins to avoid hierarchy breakage.
             if ($organization->id === $user->organization_id) {
                  // Editing self: Parent options restricted (maybe readonly?)
                  // Let's allow them to see parents but maybe we should disable changing parent_id in frontend or backend
             } else {
                  // Editing child: Parent MUST be self
                  $query->where('id', $user->organization_id);
             }
        }

        $parents = $query->get();

        return Inertia::render('Dashboard/Admin/Organization/Edit', [
            'organization' => $organization->load(['members' => function($q) {
                $q->orderBy('order', 'asc');
            }]),
            'parents' => $parents,
            'can_change_parent' => $user->isCentralAdmin(), // Helper flag
            'is_own_organization' => $user->organization_id === $organization->id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $user = auth()->user();

        // Scope Check
        if (!$user->isCentralAdmin()) {
            $isOwn = $organization->id === $user->organization_id;
            $isChild = $organization->parent_id === $user->organization_id;
            
            if (!$isOwn && !$isChild) {
                abort(403, 'Unauthorized access.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pp,dpw,dpc',
            'parent_id' => 'nullable|exists:organizations,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!$user->isCentralAdmin()) {
            // Prevent changing type or parent_id for consistency
            // Unless we want to allow DPW to move a DPC to another parent? Unlikely.
            unset($validated['type']);
            unset($validated['parent_id']); 
            
            // If editing self, cannot change is_active (anti-lockout)
            if ($organization->id === $user->organization_id) {
                 unset($validated['is_active']);
            }
        }

        if ($request->name !== $organization->name) {
             $validated['slug'] = Str::slug($validated['name']);
             // Simple unique check logic
             if (Organization::where('slug', $validated['slug'])->where('id', '!=', $organization->id)->exists()) {
                 $validated['slug'] .= '-' . time();
             }
        }

        $organization->update($validated);

        return redirect()->route('filament.admin.resources.organizations.index')
            ->with('success', 'Organisasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        $user = auth()->user();

        // Scope Check
        if (!$user->isCentralAdmin()) {
            // Can only delete CHILDREN, not self
            if ($organization->parent_id !== $user->organization_id) {
                abort(403, 'Unauthorized access or cannot delete self.');
            }
        }

        $organization->delete();

        return redirect()->route('filament.admin.resources.organizations.index')
            ->with('success', 'Organisasi berhasil dihapus.');
    }
}
