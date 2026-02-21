<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Models\HomepageSection;
use Illuminate\Support\Facades\Cache;

class SiteManagementService
{
    /**
     * Get all site settings grouped by their group name.
     */
    public function getSettings()
    {
        return Cache::rememberForever('site_settings', function () {
            return SiteSetting::all()
                ->groupBy('group')
                ->mapWithKeys(function ($items, $group) {
                    return [$group => $items->pluck('value', 'key')];
                });
        });
    }

    /**
     * Get all active homepage sections ordered by their order field.
     */
    public function getHomepageSections()
    {
        return Cache::rememberForever('homepage_sections', function () {
            return HomepageSection::active()
                ->ordered()
                ->get();
        });
    }

    /**
     * Clear all site-related caches.
     */
    public function clearCache()
    {
        static::clearStaticCache();
    }

    public static function clearStaticCache()
    {
        Cache::forget('site_settings');
        Cache::forget('homepage_sections');
    }
}
