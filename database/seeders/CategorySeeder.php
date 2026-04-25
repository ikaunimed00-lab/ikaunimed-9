<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Politik',
                'slug' => 'politik',
                'description' => 'Berita seputar dunia politik nasional dan internasional',
                'icon' => '🏛️',
                'order' => 1,
            ],
            [
                'name' => 'Ekonomi',
                'slug' => 'ekonomi',
                'description' => 'Berita ekonomi, bisnis, dan investasi',
                'icon' => '💰',
                'order' => 2,
            ],
            [
                'name' => 'Pendidikan',
                'slug' => 'pendidikan',
                'description' => 'Berita pendidikan dan kampus',
                'icon' => '🎓',
                'order' => 3,
            ],
            [
                'name' => 'Kesehatan',
                'slug' => 'kesehatan',
                'description' => 'Berita kesehatan dan medis',
                'icon' => '⚕️',
                'order' => 4,
            ],
            [
                'name' => 'Teknologi',
                'slug' => 'teknologi',
                'description' => 'Berita teknologi dan startup',
                'icon' => '💻',
                'order' => 5,
            ],
            [
                'name' => 'Olahraga',
                'slug' => 'olahraga',
                'description' => 'Berita olahraga nasional dan internasional',
                'icon' => '⚽',
                'order' => 6,
            ],
            [
                'name' => 'Hiburan',
                'slug' => 'hiburan',
                'description' => 'Berita entertainment, film, dan musik',
                'icon' => '🎬',
                'order' => 7,
            ],
            [
                'name' => 'Gaya Hidup',
                'slug' => 'gaya-hidup',
                'description' => 'Berita lifestyle, fashion, dan travel',
                'icon' => '✈️',
                'order' => 8,
            ],
            // Kontrak data blok editorial (lihat dokumentasi News Item 4):
            //   - Kategori `opini`  → menjadi sumber utama blok KolumOpini di /news.
            //   - Kategori `artikel`→ pelengkap section "Opini & Artikel" di main feed.
            // Wajib tetap ada agar query `whereHas('categories', slug='opini')`
            // tidak menghasilkan blok kosong di portal berita.
            [
                'name' => 'Opini',
                'slug' => 'opini',
                'description' => 'Kolom opini dan gagasan alumni IKA UNIMED',
                'icon' => '📝',
                'order' => 9,
            ],
            [
                'name' => 'Artikel',
                'slug' => 'artikel',
                'description' => 'Artikel panjang, esai, dan liputan khusus alumni',
                'icon' => '📰',
                'order' => 10,
            ],
            [
                'name' => 'Alumni',
                'slug' => 'alumni',
                'description' => 'Berita & kabar dari para alumni UNIMED',
                'icon' => '🎓',
                'order' => 11,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Clear cache agar perubahan langsung muncul di halaman publik
        Cache::forget('categories.all');
    }
}
