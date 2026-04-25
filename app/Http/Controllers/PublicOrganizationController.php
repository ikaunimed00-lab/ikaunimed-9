<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PublicOrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::query()
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type') && in_array($request->type, ['pp', 'dpw', 'dpc'])) {
            $query->where('type', $request->type);
        }

        $organizations = $query
            ->orderByRaw("CASE 
                WHEN type = 'pp' THEN 1 
                WHEN type = 'dpw' THEN 2 
                WHEN type = 'dpc' THEN 3 
                ELSE 4 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Organization/Index', [
            'organizations' => $organizations,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function show(Organization $organization)
    {
        abort_if(!$organization->is_active, 404);

        $allMembers = $organization->members()
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $coreMembers = $allMembers->whereNull('department')->values();
        
        $departments = $allMembers->whereNotNull('department')
            ->groupBy('department')
            ->map(function ($members, $name) {
                return [
                    'id' => md5($name), // Generate a stable ID for the key
                    'name' => $name,
                    'members' => $members->values(),
                ];
            })
            ->values();

        return Inertia::render('Public/Organization/Show', [
            'organization' => $organization,
            'members' => $coreMembers,
            'departments' => $departments,
            'news' => $organization->news()
                ->published()
                ->with(['author', 'categories'])
                ->latest('published_at')
                ->limit(6)
                ->get(),
            'agenda' => [], // Placeholder for agenda
        ]);
    }
}
