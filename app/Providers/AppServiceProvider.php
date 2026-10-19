<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\News;
use App\Models\PendaftarTeacherOfChampions;
use App\Observers\NewsObserver;
use App\Observers\TocAuditObserver;
use App\Services\AuditLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        News::observe(NewsObserver::class);
        News::observe(TocAuditObserver::class);
        Kegiatan::observe(TocAuditObserver::class);
        PendaftarTeacherOfChampions::observe(TocAuditObserver::class);

        RateLimiter::for('toc-status', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });
    }
}
