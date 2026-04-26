<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'slug',
        'image',
        'video_urls',
        'status',
        'type',
        'scope_level',
        'organization_id',
        'published_at',
        'view_count',
        'user_id',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'video_urls' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (News $news) {
            if ($news->status === 'published' && empty($news->published_at)) {
                $news->published_at = now();
            }
        });

        static::saved(function (News $news) {
            Cache::forget('news.list.page.1');
            Cache::forget('news.latest_videos');
            Cache::forget('news.popular_videos');
            Cache::forget('news.opinion_columns');
            Cache::forget('news.popular_news');
            Cache::forget('news.editors_picks');
            Cache::forget('news.popular_tags');
            Cache::forget('news.dashboard.stats');
            Cache::forget('categories.all');
        });

        static::deleted(function (News $news) {
            Cache::forget('news.list.page.1');
            Cache::forget('news.latest_videos');
            Cache::forget('news.popular_videos');
            Cache::forget('news.opinion_columns');
            Cache::forget('news.popular_news');
            Cache::forget('news.editors_picks');
            Cache::forget('news.popular_tags');
            Cache::forget('news.dashboard.stats');
            Cache::forget('categories.all');
        });
    }

    /**
     * Gunakan slug untuk route binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get reading time estimate
     */
    public function getReadingTimeAttribute(): string
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        $minutes = ceil($words / 200);
        return $minutes . ' min read';
    }

    public static function buildImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        $basename = basename($image);
        $newsPath = str_starts_with($image, 'news/') ? $image : 'news/' . $basename;

        $appendVersion = function (string $diskName, string $path, string $url): string {
            try {
                $version = Storage::disk($diskName)->lastModified($path);
                return $url.(str_contains($url, '?') ? '&' : '?').'v='.$version;
            } catch (\Throwable) {
                return $url;
            }
        };

        if (Storage::disk('public')->exists($newsPath)) {
            $url = Storage::disk('public')->url($newsPath);
            return $appendVersion('public', $newsPath, $url);
        }

        if (Storage::disk('public_images')->exists($newsPath)) {
            $url = Storage::disk('public_images')->url($newsPath);
            return $appendVersion('public_images', $newsPath, $url);
        }

        if (Storage::disk('local')->exists($newsPath)) {
            $url = url('/storage/'.$newsPath);
            return $appendVersion('local', $newsPath, $url);
        }

        // Fallback terakhir: gunakan placeholder publik agar tidak menghasilkan
        // URL gambar rusak (404) di halaman listing/detail.
        return url('/images/card_berita.png');
    }

    /**
     * Relasi penulis berita
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke organisasi
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope: berita organisasi tertentu
     */
    public function scopeOrganization($query, $orgId)
    {
        return $query->where('type', 'organization')->where('organization_id', $orgId);
    }

    /**
     * Scope: berita publik (tidak terikat organisasi atau type=public)
     */
    public function scopePublicScope($query)
    {
        return $query->where('type', 'public');
    }

    /**
     * Scope: berita yang memiliki media (foto atau video)
     */
    public function scopeHasMedia($query)
    {
        return $query->whereNotNull('image')->orWhereNotNull('video_urls');
    }

    /**
     * Relasi kategori berita
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_news',
            'news_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Relasi tag berita
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'news_tags',
            'news_id',
            'tag_id'
        )->withTimestamps();
    }

    /**
     * Scope: hanya berita yang dipublikasikan.
     *
     * KONTRAK PUBLIC FILTER (single source of truth):
     *   status = 'published'  AND
     *   published_at IS NOT NULL  AND
     *   published_at <= now()
     *
     * Setiap endpoint publik (NewsController, CategoryController, SitemapController,
     * PublicOrganizationController, dsb.) WAJIB memakai scope ini — bukan
     * `where('status', 'published')` polos — agar berita berstatus `scheduled`
     * (status=published, published_at di masa depan) tidak pernah bocor ke pembaca.
     */
    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: hanya berita draft
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: hanya berita scheduled
     */
    public function scopeScheduled($query)
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '>', now());
    }

    /**
     * Scope: urutkan by views (trending)
     */
    public function scopeTrending($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope: urutkan by latest
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Scope: filter by kategori
     */
    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('categories', fn($q) => $q->where('slug', $category));
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function getImageUrlAttribute(): ?string
    {
        return self::buildImageUrl($this->image);
    }

    /**
     * Get related news berdasarkan kategori yang sama
     */
    public function getRelatedNews($limit = 5)
    {
        return News::published()
            ->whereHas('categories', fn($query) =>
                $query->whereIn('categories.id', $this->categories->pluck('id'))
            )
            ->where('id', '!=', $this->id)
            ->take($limit)
            ->get();
    }
}
