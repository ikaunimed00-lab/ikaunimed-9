<?php

namespace App\Providers;

use App\Models\News;
use App\Observers\NewsObserver;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        News::observe(NewsObserver::class);

        RateLimiter::for('toc-login', function (Request $request) {
            $ip = $request->ip() ?? 'unknown';

            return [
                Limit::perMinute((int) config('toc.login.throttle_per_minute', 20))
                    ->by('toc-min:'.$ip)
                    ->response(fn () => response(
                        'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa menit.',
                        429,
                    )),
                Limit::perHour((int) config('toc.login.throttle_per_hour', 120))
                    ->by('toc-hour:'.$ip)
                    ->response(fn () => response(
                        'Terlalu banyak percobaan. Silakan coba lagi dalam satu jam.',
                        429,
                    )),
            ];
        });

        RateLimiter::for('toc-rejection-mail', fn (): Limit => Limit::perMinute(
            (int) config('toc.berkas.rejection_mail_per_minute', 30),
        )->by('toc-rejection-mail'));
    }
}
