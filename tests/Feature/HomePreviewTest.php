<?php

use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\KegiatanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, KegiatanSeeder::class]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('keeps the live homepage on the existing catalog page', function () {
    config(['homepage.use_portal' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Index'));
});

it('redirects guests from the homepage preview to login', function () {
    $this->get(route('home.preview'))
        ->assertRedirect(route('login'));
});

it('forbids subscribers from the homepage preview', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $this->actingAs($user)
        ->get(route('home.preview'))
        ->assertForbidden();
});

it('allows admins to review the portal draft', function () {
    Carbon::setTestNow('2026-10-09 09:00:00');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $author = User::factory()->create();

    News::query()->create([
        'title' => 'Berita terbit portal',
        'excerpt' => 'Ringkasan berita',
        'content' => 'Isi berita',
        'slug' => 'berita-terbit-portal',
        'status' => 'published',
        'type' => 'public',
        'user_id' => $author->id,
        'published_at' => now()->subHour(),
    ]);

    News::query()->create([
        'title' => 'Berita draft tidak tampil',
        'excerpt' => 'Draft',
        'content' => 'Draft',
        'slug' => 'berita-draft-portal',
        'status' => 'draft',
        'type' => 'public',
        'user_id' => $author->id,
    ]);

    $this->actingAs($admin)
        ->get(route('home.preview'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home/Portal')
            ->where('isPreview', true)
            ->has('events', 3)
            ->where('hero.slug', 'fun-walk-fun-aerobic-jumpa-party')
            ->where('hero.has_started', false)
            ->has('hero.spotlight.pimpinan', 4)
            ->where('events.1.date_label', 'Sabtu, 10 Oktober 2026')
            ->has('pimpinan', 5)
            ->where('contacts.0.whatsapp', '6282295203346')
            ->has('latestNews', 1)
            ->where('latestNews.0.title', 'Berita terbit portal')
            ->has('services')
        );
});

it('allows preview token access without login', function () {
    config(['homepage.preview_token' => 'preview-secret']);

    $this->get(route('home.preview', ['preview' => 'preview-secret']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home/Portal')
            ->where('isPreview', true)
        );
});

it('swaps the live homepage to the portal when the flag is enabled', function () {
    config(['homepage.use_portal' => true]);
    Carbon::setTestNow('2026-10-09 09:00:00');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home/Portal')
            ->where('isPreview', false)
            ->has('events', 3)
        );
});

it('redirects guests from the legacy homepage to login', function () {
    $this->get(route('home.legacy'))
        ->assertRedirect(route('login'));
});

it('forbids subscribers from the legacy homepage', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $this->actingAs($user)
        ->get(route('home.legacy'))
        ->assertForbidden();
});

it('always renders the legacy homepage for admins regardless of the portal flag', function (bool $usePortal) {
    config(['homepage.use_portal' => $usePortal]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('home.legacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('settings')
            ->has('sections')
        );
})->with([
    'portal disabled' => false,
    'portal enabled' => true,
]);

it('omits ended events from the portal payload', function () {
    config(['homepage.use_portal' => true]);
    Carbon::setTestNow('2026-11-23 09:00:00');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home/Portal')
            ->has('events', 0)
            ->where('hero.is_fallback', true)
        );
});
