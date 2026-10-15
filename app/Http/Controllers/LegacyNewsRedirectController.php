<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyNewsRedirectController extends Controller
{
    /**
     * Arahkan /berita (URL lama) ke daftar berita.
     */
    public function index(Request $request): RedirectResponse
    {
        return $this->permanentRedirect(route('news.index'), $request);
    }

    /**
     * Arahkan /berita/{slug} ke berita atau kategori dengan slug yang sama, bila ada.
     */
    public function show(Request $request, string $slug): RedirectResponse
    {
        if (News::query()->published()->where('slug', $slug)->exists()) {
            return $this->permanentRedirect(route('news.show', $slug), $request);
        }

        if (Category::query()->where('slug', $slug)->exists()) {
            return $this->permanentRedirect(route('categories.show', $slug), $request);
        }

        return $this->permanentRedirect(route('news.index'), $request);
    }

    private function permanentRedirect(string $url, Request $request): RedirectResponse
    {
        $query = $request->getQueryString();

        return redirect()->to($query ? $url.'?'.$query : $url, 301);
    }
}
