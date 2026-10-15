<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Observers\TocAuditObserver;
use App\Services\AuditLogger;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\News;
use App\Observers\NewsObserver;

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
    }
}
