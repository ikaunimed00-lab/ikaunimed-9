<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Organization;
use App\Models\Category;
use App\Models\Legalization;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Carbon\Carbon;

class NewsController extends Controller
{
    /*
    |------------------------------------------------------------------
    | PUBLIC - Homepage & Listing
    |------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        // Cache key
        $page = $request->get('page', 1);
        $cacheKey = "news.list.page.{$page}";

        $news = Cache::remember($cacheKey, 60 * 60, function () {
            return News::published()
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->paginate(12)
                ->through(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => $item->excerpt,
                    'slug' => $item->slug,
                    'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                    'view_count' => $item->view_count,
                    'author' => ['name' => $item->author?->name],
                    'categories' => $item->categories->map(fn($c) => [
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])->toArray(),
                    'published_at' => $item->published_at?->toISOString(),
                ]);
        });

        // Latest Videos for FlashContent
        $latestVideos = Cache::remember('news.latest_videos', 60 * 15, function () {
            return News::published()
                ->whereNotNull('video_urls')
                ->where('video_urls', '!=', '[]')
                ->latest('published_at')
                ->take(10)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                    'video_urls' => $item->video_urls,
                    'published_at' => $item->published_at?->toISOString(),
                ]);
        });

        // Popular Videos for VideoPopular
        $popularVideos = Cache::remember('news.popular_videos', 60 * 15, function () {
            return News::published()
                ->whereNotNull('video_urls')
                ->where('video_urls', '!=', '[]')
                ->orderBy('view_count', 'desc')
                ->take(6)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                    'video_urls' => $item->video_urls,
                    'view_count' => $item->view_count,
                    'published_at' => $item->published_at?->toISOString(),
                ]);
        });

        return Inertia::render('News/Index', compact('news', 'latestVideos', 'popularVideos'));
    }

    public function show(News $news)
    {
        // Hanya published news
        abort_if($news->status !== 'published' || $news->published_at > now(), 404);

        // Increment view count (real-time, tidak di-cache)
        $news->incrementViewCount();

        // Load relasi
        $news->load('author:id,name', 'categories:id,slug,name', 'organization:id,name,slug,type');

        // Get related news (cached)
        $relatedNews = Cache::remember(
            "news.related.{$news->id}",
            60 * 30,
            fn () => $news->getRelatedNews(5)
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                    'excerpt' => $item->excerpt,
                    'published_at' => $item->published_at?->toISOString(),
                ])
                ->toArray()
        );

        return Inertia::render('News/Show', [
            'news' => [
                'id' => $news->id,
                'title' => $news->title,
                'content' => $news->content,
                'excerpt' => $news->excerpt,
                'slug' => $news->slug,
                'image' => $news->image ? Storage::url('news/' . $news->image) : null,
                'video_urls' => $news->video_urls,
                'view_count' => $news->view_count,
                'published_at' => $news->published_at?->toISOString(),
                'created_at' => $news->created_at?->toISOString(),
                'updated_at' => $news->updated_at?->toISOString(),
                'author' => [
                    'id' => $news->author?->id,
                    'name' => $news->author?->name,
                ],
                'categories' => $news->categories->map(fn($c) => [
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->toArray(),
                'organization' => $news->organization ? [
                    'name' => $news->organization->name,
                    'slug' => $news->organization->slug,
                    'type' => $news->organization->type,
                ] : null,
            ],
            'relatedNews' => $relatedNews,
        ]);
    }

    /**
     * Halaman Berita Organisasi
     * Route: /news/organisasi/{scope}/{slug?}
     */
    public function organization(Request $request, $scope, $slug = null)
    {
        $query = News::published()
            ->where('type', 'organization')
            ->where('scope_level', $scope);

        if ($slug) {
            // Jika ada slug, filter by organization slug
            $query->whereHas('organization', fn($q) => $q->where('slug', $slug));
        }

        $news = $query->with('author:id,name', 'categories:id,slug,name', 'organization:id,name,slug')
            ->latest('published_at')
            ->paginate(12)
            ->through(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'excerpt' => $item->excerpt,
                'slug' => $item->slug,
                'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                'view_count' => $item->view_count,
                'author' => ['name' => $item->author?->name],
                'organization' => $item->organization ? [
                    'name' => $item->organization->name,
                    'slug' => $item->organization->slug,
                ] : null,
                'categories' => $item->categories->map(fn($c) => [
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->toArray(),
                'published_at' => $item->published_at?->toISOString(),
            ]);

        return Inertia::render('News/Organization', [
            'news' => $news,
            'scope' => $scope,
            'orgSlug' => $slug,
        ]);
    }

    /**
     * Halaman Media Foto (Agregasi)
     * Route: /media/foto
     */
    public function mediaPhotos(Request $request)
    {
        $photos = News::published()
            ->whereNotNull('image')
            ->latest('published_at')
            ->paginate(20)
            ->through(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'image' => Storage::url('news/' . $item->image),
                'published_at' => $item->published_at?->toISOString(),
            ]);

        return Inertia::render('Media/Photos', [
            'photos' => $photos,
        ]);
    }

    /**
     * Halaman Media Video (Agregasi)
     * Route: /media/video
     */
    public function mediaVideos(Request $request)
    {
        $videos = News::published()
            ->whereNotNull('video_urls')
            ->where('video_urls', '!=', '[]') // Ensure not empty json array
            ->latest('published_at')
            ->paginate(12)
            ->through(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'image' => $item->image ? Storage::url('news/' . $item->image) : null,
                'video_urls' => $item->video_urls,
                'published_at' => $item->published_at?->toISOString(),
            ]);

        return Inertia::render('Media/Videos', [
            'videos' => $videos,
        ]);
    }

    /**
     * API: Trending news untuk sidebar
     */
    public function trending()
    {
        $trending = Cache::remember('news.trending', 60 * 30, function () {
            return News::published()
                ->trending()
                ->take(5)
                ->select('id', 'title', 'slug', 'image', 'view_count')
                ->get()
                ->map(fn($news) => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'image' => $news->image ? Storage::url('news/' . $news->image) : null,
                    'view_count' => $news->view_count,
                ])
                ->toArray();
        });

        return response()->json($trending);
    }

    /*
    |------------------------------------------------------------------
    | DASHBOARD
    |------------------------------------------------------------------
    */

    public function dashboard()
    {
        $user = Auth::user();

        if (! $user->can('cms.news.publish')) {
            return redirect()->route('home');
        }

        // Cache dashboard stats untuk 15 menit
        $stats = Cache::remember('news.dashboard.stats', 60 * 15, function () {
            return [
                'total' => News::count(),
                'published' => News::published()->count(),
                'draft' => News::draft()->count(),
                'scheduled' => News::scheduled()->count(),
                'today' => News::published()->whereDate('published_at', Carbon::today())->count(),
                'month' => News::published()
                    ->whereMonth('published_at', now()->month)
                    ->whereYear('published_at', now()->year)
                    ->count(),
                'total_views' => News::published()->sum('view_count'),
                'trending' => News::published()
                    ->trending()
                    ->take(5)
                    ->pluck('title', 'slug')
                    ->toArray(),
            ];
        });

        $legalizationStats = Cache::remember('legalization.dashboard.stats', 60 * 15, function () {
            return [
                'total' => Legalization::count(),
                'submitted' => Legalization::where('status', 'submitted')->count(),
                'verified' => Legalization::where('status', 'verified')->count(),
                'completed' => Legalization::where('status', 'completed')->count(),
                'rejected' => Legalization::where('status', 'rejected')->count(),
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'legalizationStats' => $legalizationStats,
        ]);
    }

    /*
    |------------------------------------------------------------------
    | ADMIN INDEX (DEPRECATED - REDIRECT TO FILAMENT)
    |------------------------------------------------------------------
    */

    public function adminIndex(Request $request)
    {
        return redirect()->route('filament.admin.resources.news.index');
    }

    /*
    |------------------------------------------------------------------
    | CREATE & STORE (DEPRECATED - REDIRECT TO FILAMENT)
    |------------------------------------------------------------------
    */

    public function create()
    {
        return redirect()->route('filament.admin.resources.news.create');
    }

    public function store(StoreNewsRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Generate proper slug (unik tanpa random)
        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        $validated['user_id'] = $user->hasRole('admin') && $request->user_id
            ? $request->user_id
            : $user->id;

        // Organization Logic (Phase 2)
        if ($user->organization_id) {
            // User terikat organisasi -> force organization_id
            $validated['organization_id'] = $user->organization_id;
        } elseif ($user->hasRole('admin') && $request->organization_id) {
            // Super Admin bisa pilih organisasi (titipan)
            $validated['organization_id'] = $request->organization_id;
        }

        // Set type and scope_level based on organization_id
        if (!empty($validated['organization_id'])) {
            $validated['type'] = 'organization';
            $org = Organization::find($validated['organization_id']);
            $validated['scope_level'] = $org ? $org->type : null;
        } else {
            $validated['type'] = 'public';
            $validated['scope_level'] = null;
        }

        // Auto-publish logic
        if ($request->status === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $validated['image'] = basename($path);
        }

        $news = News::create($validated);

        // Attach categories
        if ($request->categories) {
            $news->categories()->attach($request->categories);
        }

        // Clear cache
        Cache::forget('news.list.page.1');
        Cache::forget('news.dashboard.stats');

        return redirect()->route('filament.admin.resources.news.index')
            ->with('success', 'Berita berhasil dibuat.');
    }

    /*
    |------------------------------------------------------------------
    | EDIT & UPDATE (DEPRECATED - REDIRECT TO FILAMENT)
    |------------------------------------------------------------------
    */

    public function edit(News $news)
    {
        return redirect()->route('filament.admin.resources.news.edit', $news);
    }

    public function update(UpdateNewsRequest $request, News $news)
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Update slug jika title berubah
        if ($validated['title'] !== $news->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $news->id);
        }

        // Allow admin to change author
        if ($user->hasRole('admin') && $request->user_id) {
            $validated['user_id'] = $request->user_id;
        }

        // Organization Logic (Phase 2)
        if ($user->organization_id) {
            // User terikat organisasi -> tidak bisa ubah organization_id
            unset($validated['organization_id']);
            // Keep existing type/scope logic (handled by store usually, but if update needs re-check?)
            // Usually organization doesn't change for user.
        } elseif ($user->hasRole('admin')) {
            // Super Admin bisa ubah organisasi
            // Jika organization_id tidak dikirim (null), artinya jadi global
            $validated['organization_id'] = $request->input('organization_id');
        }

        // Recalculate type/scope if organization_id is present in validated (meaning it might have changed)
        // Or if we need to enforce consistency.
        // For admin update, organization_id might be set or null.
        if (array_key_exists('organization_id', $validated)) {
             if (!empty($validated['organization_id'])) {
                $validated['type'] = 'organization';
                $org = Organization::find($validated['organization_id']);
                $validated['scope_level'] = $org ? $org->type : null;
            } else {
                $validated['type'] = 'public';
                $validated['scope_level'] = null;
            }
        }

        // Handle published_at
        if ($validated['status'] === 'published' && !$validated['published_at']) {
            $validated['published_at'] = $news->published_at ?? now();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($news->image) {
                Storage::disk('public')->delete('news/' . $news->image);
            }
            $path = $request->file('image')->store('news', 'public');
            $validated['image'] = basename($path);
        }

        $news->update($validated);

        // Sync categories
        if ($request->has('categories')) {
            $news->categories()->sync($request->categories);
        }

        // Clear cache
        Cache::forget("news.related.{$news->id}");
        Cache::forget('news.list.page.1');
        Cache::forget('news.dashboard.stats');

        return redirect()->route('filament.admin.resources.news.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    /*
    |------------------------------------------------------------------
    | DELETE - Soft Delete untuk Audit Trail
    |------------------------------------------------------------------
    */

    public function destroy(News $news)
    {
        $this->authorize('delete', $news);

        // Image akan tetap di storage (soft delete hanya menghapus database record)
        // Opsional: delete image jika ingin permanent deletion
        // if ($news->image) {
        //     Storage::disk('public')->delete('news/' . $news->image);
        // }

        $news->delete();

        Cache::forget('news.list.page.1');
        Cache::forget('news.dashboard.stats');

        return redirect()->route('filament.admin.resources.news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('deleteAny', News::class);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:news,id',
        ]);

        $count = News::whereIn('id', $validated['ids'])->delete();

        Cache::forget('news.list.page.1');
        Cache::forget('news.dashboard.stats');

        return redirect()->route('filament.admin.resources.news.index')
            ->with('success', "{$count} berita berhasil dihapus.");
    }

    /*
    |------------------------------------------------------------------
    | HELPER METHODS
    |------------------------------------------------------------------
    */

    /**
     * Generate unique slug dari title
     * Lebih baik dari slug-random approach
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $exists = News::where('slug', $slug);

            if ($excludeId) {
                $exists->where('id', '!=', $excludeId);
            }

            if (!$exists->exists()) {
                break;
            }

            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
