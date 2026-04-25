<?php

namespace App\Observers;

use App\Models\News;
use Illuminate\Support\Facades\Cache;

class NewsObserver
{
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        $this->clearNewsCache($news);
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(News $news): void
    {
        $this->clearNewsCache($news);
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(News $news): void
    {
        $this->clearNewsCache($news);
    }

    /**
     * Handle the News "restored" event.
     */
    public function restored(News $news): void
    {
        $this->clearNewsCache($news);
    }

    /**
     * Handle the News "force deleted" event.
     */
    public function forceDeleted(News $news): void
    {
        $this->clearNewsCache($news);
    }

    /**
     * Clear all caches related to News.
     */
    protected function clearNewsCache(News $news): void
    {
        // Clear listing caches (Page 1 is most critical)
        Cache::forget('news.list.page.1');
        
        // Clear widget/component caches
        Cache::forget('news.latest_videos');
        Cache::forget('news.popular_videos');
        Cache::forget('news.opinion_columns');
        Cache::forget('news.popular_news');
        Cache::forget('news.popular_tags');
        Cache::forget('news.trending'); // Used in API/Sidebar
        
        // Clear specific news cache
        Cache::forget("news.related.{$news->id}");

        // Clear sitemaps
        Cache::forget('sitemap.news');
        Cache::forget('sitemap.google-news');
    }
}
