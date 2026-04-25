<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Tampilkan halaman kategori dengan berita
     */
    public function show(Category $category, Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "category.{$category->slug}.page.{$page}";

        $news = Cache::remember($cacheKey, 60 * 60, function () use ($category) {
            return $category->news()
                ->published()
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->paginate(12)
                ->through(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => $item->excerpt,
                    'slug' => $item->slug,
                    'image' => News::buildImageUrl($item->image),
                    'view_count' => $item->view_count,
                    'author' => ['name' => $item->author?->name],
                    'published_at' => $item->published_at?->toISOString(),
                ]);
        });

        return Inertia::render('Category/Show', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
            ],
            'news' => $news,
        ]);
    }

    /**
     * API: Get semua kategori dengan news count (untuk navigation)
     */
    public function index()
    {
        $allowedSlugs = [
            'politik',
            'ekonomi',
            'pendidikan',
            'kesehatan',
            'teknologi',
            'olahraga',
            'hiburan',
            'gaya-hidup',
        ];

        $categories = Cache::remember('categories.all', 60 * 60, function () use ($allowedSlugs) {
            return Category::select('id', 'name', 'slug', 'icon', 'order')
                ->whereIn('slug', $allowedSlugs)
                ->withCount([
                    'news' => fn($query) => $query->published()
                ])
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        });

        return response()->json($categories);
    }

    /**
     * API: Get popular news per kategori
     */
    public function trendingByCategory(Category $category)
    {
        $news = Cache::remember(
            "category.{$category->slug}.trending",
            60 * 30,
            fn () => $category->news()
                ->published()
                ->trending()
                ->take(5)
                ->get(['id', 'title', 'slug', 'image', 'view_count', 'published_at'])
                ->map(fn ($item) => [
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'image' => News::buildImageUrl($item->image),
                    'view_count' => $item->view_count,
                ])
                ->toArray()
        );

        return response()->json($news);
    }
}
