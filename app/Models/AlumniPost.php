<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AlumniPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'category',
        'image',
        'map_location',
        'status',
        'rejection_note',
        'moderated_by',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Available categories
     */
    public const CATEGORIES = [
        'prestasi' => 'Alumni Berprestasi',
        'pernikahan' => 'Pernikahan Alumni',
        'wafat' => 'Alumni Wafat',
        'usaha' => 'Usaha Alumni',
        'beasiswa' => 'Beasiswa Alumni',
        'karir' => 'Karir Alumni',
        'lainnya' => 'Lainnya',
    ];

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    /**
     * Boot method untuk auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                
                // Ensure unique slug
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->exists()) {
                    $post->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Relasi dengan User (penulis)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi dengan User (moderator)
     */
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope: hanya post yang published
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->orderBy('published_at', 'desc');
    }

    /**
     * Scope: filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: pending posts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->orderBy('created_at', 'asc');
    }

    /**
     * Check if post can be edited by user
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->can('alumni.moderate')) {
            return true;
        }

        // Penulis hanya bisa edit miliknya sendiri yang masih pending
        if ($this->user_id === $user->id && $this->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Check if post can be deleted by user
     */
    public function canBeDeletedBy(User $user): bool
    {
        if ($user->can('alumni.moderate')) {
            return true;
        }

        // Penulis hanya bisa hapus miliknya sendiri yang masih pending
        if ($this->user_id === $user->id && $this->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return static::categories()[$this->category] ?? $this->category;
    }
}
