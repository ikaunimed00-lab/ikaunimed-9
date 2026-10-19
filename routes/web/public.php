<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReuniAkbarController;
use App\Http\Controllers\TeacherOfChampionsBuktiController;
use App\Http\Controllers\TeacherOfChampionsController;
use App\Http\Controllers\TeacherOfChampionsRegistrationController;
use App\Http\Controllers\TocStatusCheckController;
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

    Route::get('/preview/home', [HomeController::class, 'preview'])
        ->middleware('homepage.preview')
        ->name('home.preview');

    Route::get('/home-lama', [HomeController::class, 'legacy'])
        ->middleware('homepage.preview')
        ->name('home.legacy');

    Route::get('/reuni-akbar-2026', [ReuniAkbarController::class, 'show'])->name('reuni-akbar.show');

    Route::get('/toc', [TeacherOfChampionsController::class, 'show'])->name('teacher-of-champions.landing');
    Route::permanentRedirect('/teacher-of-champions', '/toc');

    Route::get('/teacher-of-champions/daftar', [TeacherOfChampionsRegistrationController::class, 'create'])
        ->name('teacher-of-champions.create');
    Route::post('/teacher-of-champions/daftar', [TeacherOfChampionsRegistrationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('teacher-of-champions.store');
    Route::get('/teacher-of-champions/daftar/sukses', [TeacherOfChampionsRegistrationController::class, 'success'])
        ->name('teacher-of-champions.success');
    Route::get('/teacher-of-champions/daftar/sukses/{pendaftar}', [TeacherOfChampionsRegistrationController::class, 'showSuccess'])
        ->name('teacher-of-champions.success.show');
    Route::get('/teacher-of-champions/daftar/bukti/{pendaftar}', TeacherOfChampionsBuktiController::class)
        ->name('teacher-of-champions.bukti');

    Route::get('/toc/cek-status', [TocStatusCheckController::class, 'show'])
        ->name('teacher-of-champions.status');
    Route::post('/toc/cek-status', [TocStatusCheckController::class, 'check'])
        ->middleware('throttle:toc-status')
        ->name('teacher-of-champions.status.check');

    require __DIR__.'/public/news.php';
    require __DIR__.'/public/shop.php';
    require __DIR__.'/public/categories.php';
    require __DIR__.'/public/info.php';
    require __DIR__.'/public/organizations.php';
    require __DIR__.'/public/community.php';
    require __DIR__.'/public/jobs.php';
    require __DIR__.'/public/courses.php';
    require __DIR__.'/public/scholarships.php';
    require __DIR__.'/public/partnerships.php';
});
