<?php

use App\Http\Controllers\OAuthController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap/news.xml', [SitemapController::class, 'news']);
Route::get('/sitemap/categories.xml', [SitemapController::class, 'categories']);
Route::get('/sitemap/google-news.xml', [SitemapController::class, 'googleNews']);

require __DIR__ . '/system/tripay.php';

Route::get('/auth/google', [OAuthController::class, 'googleRedirect'])->name('oauth.google');
Route::get('/auth/google/callback', [OAuthController::class, 'googleCallback'])
    ->name('oauth.google.callback');
