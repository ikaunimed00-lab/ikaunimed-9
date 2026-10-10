<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Sitemap index - semua URLs
     */
    public function index()
    {
        $content = Cache::remember('sitemap.index', 60 * 24, function () {
            return view('sitemap.index')->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Sitemap berita - untuk SEO (12 jam cache)
     */
    public function news()
    {
        $content = Cache::remember('sitemap.news', 60 * 12, function () {
            $news = News::published()
                ->select('id', 'slug', 'published_at', 'updated_at')
                ->latest('published_at')
                ->get();

            return view('sitemap.news', compact('news'))->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=43200');
    }

    /**
     * Sitemap kategori - untuk SEO
     */
    public function categories()
    {
        $content = Cache::remember('sitemap.categories', 60 * 24, function () {
            $categories = Category::select('slug', 'updated_at')
                ->orderBy('order')
                ->get();

            return view('sitemap.categories', compact('categories'))->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Sitemap halaman statis publik (beranda, landing kegiatan).
     */
    public function pages(): Response
    {
        $content = Cache::remember('sitemap.pages', 60 * 24, function () {
            $pages = [
                ['path' => '/', 'changefreq' => 'daily', 'priority' => '1.0'],
                ['path' => '/toc', 'changefreq' => 'weekly', 'priority' => '0.9'],
                ['path' => '/teacher-of-champions/daftar', 'changefreq' => 'weekly', 'priority' => '0.8'],
                ['path' => '/reuni-akbar-2026', 'changefreq' => 'monthly', 'priority' => '0.6'],
            ];

            return view('sitemap.pages', compact('pages'))->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Google News sitemap - untuk Google News indexing (2 jam cache)
     * Spec: https://support.google.com/news/answer/74288
     */
    public function googleNews()
    {
        $content = Cache::remember('sitemap.google-news', 60 * 2, function () {
            // Google News hanya menerima berita dari 2 hari terakhir
            $news = News::published()
                ->where('published_at', '>=', Carbon::now()->subDays(2))
                ->select('id', 'title', 'slug', 'published_at', 'updated_at')
                ->orderBy('published_at', 'desc')
                ->take(1000)
                ->get();

            return view('sitemap.google-news', compact('news'))->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=7200');
    }
}
