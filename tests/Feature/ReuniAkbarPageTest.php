<?php

use App\Models\Kegiatan;
use Database\Seeders\KegiatanSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the reuni akbar schedule, route map and Teacher of Champions publicly', function () {
    $this->seed(KegiatanSeeder::class);

    $this->get(route('reuni-akbar.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ReuniAkbar/Show')
            ->where('funWalk.slug', 'fun-walk-fun-aerobic-jumpa-party')
            ->where('funWalk.route_map_image', '/images/fun-walk-rute-2026.png')
            ->where('catur.slug', 'turnamen-catur-nasional-2026')
            ->where('catur.location', 'VIP Room Serba Guna UNIMED, Medan')
            ->where('catur.hero_image', '/images/event-catur-wide.jpg')
            ->has('catur.spotlight.pimpinan', 2)
            ->where('teacherOfChampions.hero_image', '/images/teacher-of-champions-poster.jpg')
            ->where('teacherOfChampions.cta_url', '/teacher-of-champions/daftar')
            ->has('teacherOfChampions.schedule', 5)
            ->where('bannerImage', '/images/grand-reunion-banner-2026-koreksi.jpg')
            ->has('contacts', 2)
        );
});

it('renders share metadata server-side for WhatsApp previews', function () {
    $this->seed(KegiatanSeeder::class);

    $this->get(route('reuni-akbar.show'))
        ->assertOk()
        ->assertSee(url('/images/grand-reunion-banner-2026-koreksi.jpg'), false)
        ->assertSee('Grand Reunion IKA UNIMED 2026', false);
});

it('falls back to the bundled route map when the fun walk has none', function () {
    $this->seed(KegiatanSeeder::class);

    Kegiatan::query()->where('slug', 'fun-walk-fun-aerobic-jumpa-party')->update(['route_map_image' => null]);

    $this->get(route('reuni-akbar.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('funWalk.route_map_image', '/images/fun-walk-rute-2026.png')
        );
});

it('renders without kegiatan data', function () {
    $this->get(route('reuni-akbar.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ReuniAkbar/Show')
            ->where('funWalk', null)
            ->where('catur', null)
            ->where('teacherOfChampions', null)
        );
});
