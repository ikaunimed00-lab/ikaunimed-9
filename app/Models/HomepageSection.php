<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = [
        'type',
        'title',
        'slug',
        'content',
        'order',
        'is_active',
        'scope_type',
        'scope_id',
    ];

    protected $casts = [
        'content' => 'json',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function ($section) {
            \App\Services\SiteManagementService::clearStaticCache();
        });

        static::deleted(function ($section) {
            \App\Services\SiteManagementService::clearStaticCache();
        });
    }

    /**
     * Scope a query to only include active sections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to sort by order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
