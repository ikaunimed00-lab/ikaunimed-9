<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Organization;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Legalization;
use App\Models\SiteSetting;
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

        // Helper untuk transform news item
        $transformNews = fn ($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'excerpt' => $item->excerpt,
            'slug' => $item->slug,
            'image' => News::buildImageUrl($item->image),
            'view_count' => $item->view_count,
            'author' => ['name' => $item->author?->name],
            'categories' => $item->categories->map(fn($c) => [
                'name' => $c->name,
                'slug' => $c->slug,
            ])->toArray(),
            'published_at' => $item->published_at?->toISOString(),
            'reading_time' => $item->reading_time,
        ];

        // 1. Breaking News (Running Text - Khusus Berita Alumni)
        $breakingNews = Cache::remember('news.breaking', 60 * 5, function () {
            return News::published()
                ->whereHas('categories', fn($q) => $q->whereIn('slug', ['alumni', 'kabar-alumni', 'pendidikan', 'sosial']))
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                ]);
        });

        // 2. Hero News (Top 3 Latest)
        $heroNews = Cache::remember('news.hero', 60 * 5, function () use ($transformNews) {
            return News::published()
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->take(3)
                ->get()
                ->map($transformNews);
        });

        // ID yang sudah muncul di Hero, exclude dari list lain agar variatif
        $excludeIds = collect($heroNews)->pluck('id')->toArray();

        // 3. Alumni News (Prioritas kategori alumni/pendidikan)
        $alumniNews = Cache::remember('news.section.alumni', 60 * 5, function () use ($excludeIds, $transformNews) {
            return News::published()
                ->whereNotIn('id', $excludeIds)
                ->whereHas('categories', fn($q) => $q->whereIn('slug', ['alumni', 'kabar-alumni', 'pendidikan', 'sosial']))
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->take(6)
                ->get()
                ->map($transformNews);
        });

        // 4. Opinion/Artikel News (Prioritas kategori opini/ekonomi/teknologi)
        $opinionNews = Cache::remember('news.section.opinion', 60 * 5, function () use ($excludeIds, $transformNews) {
            return News::published()
                ->whereNotIn('id', $excludeIds)
                ->whereHas('categories', fn($q) => $q->whereIn('slug', ['opini', 'artikel', 'ekonomi', 'teknologi', 'gaya-hidup']))
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->take(5)
                ->get()
                ->map($transformNews);
        });

        // 5. General News (Paginated) - Sisa berita
        $news = Cache::remember($cacheKey, 60 * 60, function () use ($excludeIds, $transformNews) {
            return News::published()
                ->whereNotIn('id', $excludeIds)
                ->with('author:id,name', 'categories:id,slug,name')
                ->latest()
                ->paginate(12)
                ->through($transformNews);
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
                    'image' => News::buildImageUrl($item->image),
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
                    'image' => News::buildImageUrl($item->image),
                    'video_urls' => $item->video_urls,
                    'view_count' => $item->view_count,
                    'published_at' => $item->published_at?->toISOString(),
                ]);
        });

        // Opinion Columns for KolumOpini (berdasarkan kategori 'opini' jika ada)
        $opinionColumns = Cache::remember('news.opinion_columns', 60 * 15, function () {
            return News::published()
                ->whereHas('categories', fn($q) => $q->where('slug', 'opini'))
                ->with(['author:id,name', 'categories:id,name,slug'])
                ->latest('published_at')
                ->take(8)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'author' => $item->author?->name,
                    'category' => $item->categories->first()?->name,
                ]);
        });

        // Popular News (trending by view_count) — sumber data BeritaPopuler.
        // Kontrak: 10 berita teratas berdasarkan jumlah pembaca, status published.
        $popularNews = Cache::remember('news.popular_news', 60 * 30, function () {
            return News::published()
                ->trending()
                ->select('id', 'title', 'slug', 'image', 'view_count', 'published_at')
                ->take(10)
                ->get()
                ->map(fn ($news) => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'image' => News::buildImageUrl($news->image),
                    'view_count' => $news->view_count,
                    'published_at' => $news->published_at?->toISOString(),
                ]);
        });

        // Editor's Picks (kurasi) — sumber data EditorsPicks.
        // Kontrak: berita ber-image terbaru, EXCLUDE hero & top-3 popular agar
        // tidak duplikasi dengan blok lain. Ini "pilihan kurasi" — bukan
        // ranking otomatis. Bila nanti ada kolom `is_editors_pick` di tabel
        // news, query ini bisa diganti tanpa mengubah kontrak prop.
        $editorsPickExcludeIds = collect($heroNews)
            ->pluck('id')
            ->merge($popularNews->take(3)->pluck('id'))
            ->unique()
            ->values()
            ->toArray();

        $editorsPicks = Cache::remember('news.editors_picks', 60 * 30, function () use ($editorsPickExcludeIds) {
            return News::published()
                ->whereNotIn('id', $editorsPickExcludeIds)
                ->whereNotNull('image')
                ->select('id', 'title', 'slug', 'image', 'published_at')
                ->latest('published_at')
                ->take(8)
                ->get()
                ->map(fn ($news) => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'image' => News::buildImageUrl($news->image),
                    'published_at' => $news->published_at?->toISOString(),
                ]);
        });

        // Popular Tags untuk TagPopuler (berdasarkan jumlah berita terbit)
        $popularTags = Cache::remember('news.popular_tags', 60 * 60, function () {
            return Tag::withCount(['news' => fn($q) => $q->published()])
                ->orderBy('news_count', 'desc')
                ->take(30)
                ->get()
                ->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'count' => $tag->news_count,
                ]);
        });

        $adsConfig = $this->buildAdsConfig();

        return Inertia::render('News/Index', [
            'news' => $news,
            'breakingNews' => $breakingNews,
            'heroNews' => $heroNews,
            'alumniNews' => $alumniNews,
            'opinionNews' => $opinionNews,
            'latestVideos' => $latestVideos,
            'popularVideos' => $popularVideos,
            'opinionColumns' => $opinionColumns,
            'popularNews' => $popularNews,
            'editorsPicks' => $editorsPicks,
            'popularTags' => $popularTags,
            'ads' => $adsConfig,
        ]);
    }

    public function show(News $news)
    {
        // Hanya berita yang sudah benar-benar tayang.
        // Kontrak harus konsisten dengan scope News::published():
        //   status === 'published' AND published_at IS NOT NULL AND published_at <= now()
        abort_if(
            $news->status !== 'published'
                || $news->published_at === null
                || $news->published_at->greaterThan(now()),
            404
        );

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
                    'image' => News::buildImageUrl($item->image),
                    'excerpt' => $item->excerpt,
                    'published_at' => $item->published_at?->toISOString(),
                ])
                ->toArray()
        );

        $adsConfig = $this->buildAdsConfig();

        return Inertia::render('News/Show', [
            'news' => [
                'id' => $news->id,
                'title' => $news->title,
                'content' => $news->content,
                'excerpt' => $news->excerpt,
                'slug' => $news->slug,
                'image' => News::buildImageUrl($news->image),
                'video_urls' => $news->video_urls,
                'view_count' => $news->view_count,
                'published_at' => $news->published_at?->toISOString(),
                'created_at' => $news->created_at?->toISOString(),
                'updated_at' => $news->updated_at?->toISOString(),
                'reading_time' => $news->reading_time,
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
            'ads' => $adsConfig,
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
                'image' => News::buildImageUrl($item->image),
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
                'image' => News::buildImageUrl($item->image),
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
                'image' => News::buildImageUrl($item->image),
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
                    'image' => News::buildImageUrl($news->image),
                    'view_count' => $news->view_count,
                ])
                ->toArray();
        });

        return response()->json($trending);
    }

    private function buildNewsImageUrl(?string $image): ?string
    {
        return News::buildImageUrl($image);
    }

    /**
     * Bentuk payload `ads` yang konsisten untuk semua halaman News publik.
     *
     * KEBIJAKAN IKLAN (Fase 1 — News modul):
     *   - `enabled`   : master switch dari SiteSetting `ads_enabled`. Ketika
     *                   `false`, semua komponen Ad* di frontend WAJIB tidak
     *                   merender slot (cukup return null / tampilkan
     *                   placeholder ramah). Ini menghindari kondisi review
     *                   AdSense (Google bisa menolak situs jika `<ins>` kosong
     *                   muncul tanpa konten resmi).
     *   - `provider`  : `adsense` | `adsterra` | `none`. Saat ini hanya
     *                   `adsense` yang punya komponen aktif; `adsterra`
     *                   ditangani lewat snippet HTML di `app.blade.php`.
     *   - Slot id     : satu sumber tunggal di SiteSetting agar satu titik
     *                   ubah → semua halaman ikut. Slot kosong = tidak render.
     */
    private function buildAdsConfig(): array
    {
        return [
            'enabled' => (bool) SiteSetting::getValue('ads_enabled', false),
            'provider' => SiteSetting::getValue('ads_provider_primary', 'adsense'),
            'leaderboard_slot' => SiteSetting::getValue('adsense_slot_banner'),
            'sidebar_1_slot' => SiteSetting::getValue('adsense_slot_sidebar_1'),
            'sidebar_2_slot' => SiteSetting::getValue('adsense_slot_sidebar_2'),
            'inline_article_slot' => SiteSetting::getValue('adsense_slot_inline_article'),
            'infeed_slot' => SiteSetting::getValue('adsense_slot_list_item'),
        ];
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
