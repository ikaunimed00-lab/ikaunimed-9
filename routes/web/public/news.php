<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/organisasi/{scope}/{slug?}', [NewsController::class, 'organization'])
    ->where('scope', 'pp|dpw|dpc')
    ->name('news.organization');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/api/news/trending', [NewsController::class, 'trending'])->name('news.trending');
Route::get('/media/foto', [NewsController::class, 'mediaPhotos'])->name('media.photos');
Route::get('/media/video', [NewsController::class, 'mediaVideos'])->name('media.videos');
