<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AlumniPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlumniPostModerationController extends Controller
{
    public function index(Request $request)
    {
        $query = AlumniPost::with('user')
            ->when($request->status && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->category, function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->search . '%')
                        ->orWhereHas('user', function ($q) use ($request) {
                            $q->where('name', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->orderBy('created_at', 'desc');

        $posts = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => AlumniPost::count(),
            'pending' => AlumniPost::where('status', 'pending')->count(),
            'published' => AlumniPost::where('status', 'published')->count(),
            'rejected' => AlumniPost::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Dashboard/Editor/AlumniPost/Moderation', [
            'posts' => $posts,
            'stats' => $stats,
            'categories' => AlumniPost::CATEGORIES,
            'filters' => $request->only(['status', 'category', 'search']),
        ]);
    }

    public function show(AlumniPost $alumniPost)
    {
        $alumniPost->load('user');

        return Inertia::render('Dashboard/Editor/AlumniPost/Show', [
            'post' => $alumniPost,
            'categories' => AlumniPost::CATEGORIES,
        ]);
    }

    public function approve(AlumniPost $alumniPost)
    {
        $alumniPost->update([
            'status' => 'published',
            'published_at' => now(),
            'rejection_note' => null,
        ]);

        return redirect()->back()->with('success', 'Kabar alumni berhasil dipublikasikan');
    }

    public function reject(Request $request, AlumniPost $alumniPost)
    {
        $validated = $request->validate([
            'rejection_note' => 'required|string|min:10',
        ]);

        $alumniPost->update([
            'status' => 'rejected',
            'rejection_note' => $validated['rejection_note'],
        ]);

        return redirect()->back()->with('success', 'Kabar alumni ditolak');
    }

    public function destroy(AlumniPost $alumniPost)
    {
        $alumniPost->delete();

        return redirect()->route('filament.admin.resources.alumni-posts.index')
            ->with('success', 'Kabar alumni berhasil dihapus');
    }
}