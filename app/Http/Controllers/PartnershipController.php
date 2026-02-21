<?php

namespace App\Http\Controllers;

use App\Models\Partnership;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnershipController extends Controller
{
    public function index(Request $request)
    {
        $query = Partnership::query()
            ->where('status', 'active')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category', $category);
            })
            ->latest();

        return Inertia::render('Partnership/Index', [
            'partnerships' => $query->paginate(9)->withQueryString(),
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function show(Partnership $partnership)
    {
        if ($partnership->status !== 'active' && ! auth()->user()?->can('cms.job.publish')) {
            abort(404);
        }

        return Inertia::render('Partnership/Show', [
            'partnership' => $partnership,
        ]);
    }
}
