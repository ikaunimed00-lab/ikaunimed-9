<?php

use App\Models\HomepageSection;
use Database\Seeders\KegiatanSeeder;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

it('hides top bar links that point to routes that do not exist', function () {
    $this->seed(KegiatanSeeder::class);
    Cache::forget('global_topbar_content');

    HomepageSection::query()->create([
        'type' => 'topbar',
        'title' => 'Top Bar Navigation',
        'slug' => 'topbar',
        'order' => 0,
        'is_active' => true,
        'content' => [
            'links' => [
                ['label' => 'Beasiswa', 'href' => '/beasiswa', 'icon' => 'GraduationCap'],
                ['label' => 'Donasi', 'href' => '/donasi', 'icon' => 'Heart'],
                ['label' => 'Reuni', 'href' => '/reuni-akbar-2026', 'icon' => 'Heart'],
                ['label' => 'Instagram', 'href' => 'https://instagram.com/ikaunimed', 'icon' => 'Heart'],
            ],
            'right_label' => 'Area Alumni',
            'right_href' => '/login',
        ],
    ]);

    $this->get(route('reuni-akbar.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('topbar.links', fn ($links) => collect($links)->pluck('label')->all() === ['Beasiswa', 'Reuni', 'Instagram'])
            ->where('topbar.right_href', '/login')
        );
});
