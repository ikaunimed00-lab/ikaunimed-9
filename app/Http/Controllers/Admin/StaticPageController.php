<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaticPageController extends Controller
{
    public function index()
    {
        $pages = StaticPage::orderBy('title')->get();

        return Inertia::render('Dashboard/Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function edit(StaticPage $page)
    {
        return Inertia::render('Dashboard/Admin/Pages/Edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, StaticPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $page->update($validated);

        return redirect()->route('dashboard.pages.index')
            ->with('success', 'Halaman berhasil diperbarui.');
    }
}
