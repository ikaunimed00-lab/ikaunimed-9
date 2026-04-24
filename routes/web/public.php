<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function (string $path) {
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->response($path, headers: [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    if (str_starts_with($path, 'news/') && Storage::disk('local')->exists($path)) {
        return Storage::disk('local')->response($path, headers: [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    abort(404);
})->where('path', '.*');

Route::middleware([HandleInertiaRequests::class])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    require __DIR__ . '/public/news.php';
    require __DIR__ . '/public/shop.php';
    require __DIR__ . '/public/categories.php';
    require __DIR__ . '/public/info.php';
    require __DIR__ . '/public/organizations.php';
    require __DIR__ . '/public/community.php';
    require __DIR__ . '/public/jobs.php';
    require __DIR__ . '/public/courses.php';
    require __DIR__ . '/public/scholarships.php';
    require __DIR__ . '/public/partnerships.php';
});
