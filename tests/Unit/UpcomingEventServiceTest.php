<?php

use App\Models\Kegiatan;
use App\Services\UpcomingEventService;
use Carbon\Carbon;
use Database\Seeders\KegiatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(KegiatanSeeder::class);
    $this->service = app(UpcomingEventService::class);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('stores seeded schedules in UTC and exposes them in WIB', function () {
    $funWalk = Kegiatan::query()->where('slug', 'fun-walk-fun-aerobic-jumpa-party')->firstOrFail();

    expect($funWalk->getRawOriginal('starts_at'))->toBe('2026-10-09 23:00:00');

    $payload = $this->service->toArray($funWalk);

    expect($payload['starts_at'])->toBe('2026-10-10T06:00:00+07:00')
        ->and($payload['date_label'])->toBe('Sabtu, 10 Oktober 2026')
        ->and($payload['time_label'])->toBe('06.00 WIB')
        ->and($payload['time_range_label'])->toBe('06.00 WIB – selesai')
        ->and($payload['group_key'])->toBe('2026-10-10')
        ->and($payload['theme'])->toBe('hijau')
        ->and($payload['image'])->toBe('/images/event-fun-walk-card.jpg')
        ->and($payload['image_sm'])->toBe('/images/event-fun-walk-card-sm.jpg')
        ->and($payload['route_map_image'])->toBe('/images/fun-walk-rute-2026.png')
        ->and($payload['schedule'][0])->toBe(['time' => '06.00-07.00', 'label' => 'Registrasi & pembagian kupon door prize (di lokasi)'])
        ->and($payload['spotlight']['eyebrow'])->toBe('Grand Reunion')
        ->and(array_column($payload['spotlight']['pimpinan'], 'key'))->toBe(['rektor', 'ketua-senat', 'ketua-umum', 'sekjen']);
});

it('is idempotent when the seeder runs again', function () {
    $this->seed(KegiatanSeeder::class);

    expect(Kegiatan::query()->count())->toBe(3);
});

it('lists active kegiatan ordered by start time', function () {
    Carbon::setTestNow('2026-10-09 09:00:00');

    $events = $this->service->active()->map(fn (Kegiatan $kegiatan) => $this->service->toArray($kegiatan));

    expect($events->pluck('slug')->all())->toBe([
        'fun-walk-fun-aerobic-jumpa-party',
        'turnamen-catur-nasional-2026',
        'teacher-of-champions',
    ])
        ->and($events[0]['group_key'])->toBe($events[1]['group_key'])
        ->and($events[1]['time_label'])->toBe('08.00 WIB')
        ->and($events[2]['is_multi_day'])->toBeTrue()
        ->and($events[2]['theme'])->toBe('navy-emas')
        ->and($events[2]['time_label'])->toBeNull()
        ->and($events[2]['date_label'])->toBe('12 Oktober – 22 November 2026')
        ->and($events[2]['cta_url'])->toBe('/teacher-of-champions/daftar');
});

it('uses the featured kegiatan as hero with a pending countdown', function () {
    Carbon::setTestNow('2026-10-09 09:00:00');

    $hero = $this->service->hero();

    expect($hero['slug'])->toBe('fun-walk-fun-aerobic-jumpa-party')
        ->and($hero['is_fallback'])->toBeFalse()
        ->and($hero['has_started'])->toBeFalse();
});

it('switches the hero to Teacher of Champions after the October 10 events end', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-12 00:30', 'Asia/Jakarta')->utc());

    $hero = $this->service->hero();

    expect($this->service->active()->pluck('slug')->all())->toBe(['teacher-of-champions'])
        ->and($hero['slug'])->toBe('teacher-of-champions')
        ->and($hero['theme'])->toBe('navy-emas')
        ->and($hero['hero_image'])->toBe('/images/teacher-of-champions-poster.jpg')
        ->and($hero['has_started'])->toBeTrue()
        ->and($hero['schedule'])->toHaveCount(7);
});

it('keeps the October 10 events active until 23:59 WIB', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-10 23:58', 'Asia/Jakarta')->utc());

    expect($this->service->active())->toHaveCount(3);
});

it('falls back to the organization hero when every kegiatan has ended', function () {
    Carbon::setTestNow('2026-11-23 09:00:00');

    expect($this->service->active())->toBeEmpty()
        ->and($this->service->hero()['is_fallback'])->toBeTrue()
        ->and($this->service->hero()['title'])->toBe('IKA UNIMED');
});

it('excludes inactive kegiatan and refreshes the cache after saving', function () {
    Carbon::setTestNow('2026-10-09 09:00:00');

    expect($this->service->active())->toHaveCount(3);

    Kegiatan::query()->where('slug', 'turnamen-catur-nasional-2026')->firstOrFail()->update(['is_active' => false]);

    expect($this->service->active()->pluck('slug')->all())->not->toContain('turnamen-catur-nasional-2026');

    Kegiatan::query()->where('slug', 'teacher-of-champions')->firstOrFail()->delete();

    expect($this->service->active())->toHaveCount(1);
});

it('resolves pimpinan from config in the requested order', function () {
    expect($this->service->pimpinan())->toHaveCount(5)
        ->and(array_column($this->service->pimpinan(['ketua-umum', 'rektor', 'unknown']), 'key'))->toBe(['ketua-umum', 'rektor']);
});

it('uses the full academic titles and uniform tile photos for pimpinan', function () {
    $pimpinan = collect($this->service->pimpinan())->keyBy('key');

    expect($pimpinan['rektor']['name'])->toBe('Prof. Dr. Baharuddin, ST., M.Pd.')
        ->and($pimpinan['ketua-senat']['name'])->toBe('Prof. Dr. Martina Restuati, M.Si.')
        ->and($pimpinan['ketua-umum']['image'])->toBe('/images/tokoh/ketua-umum-jafaruddin-harahap.png')
        ->and($pimpinan['ketua-harian']['image'])->toBe('/images/tokoh/ketua-harian-reza-faisal.png')
        ->and($pimpinan['sekjen']['image'])->toBe('/images/tokoh/sekjen-suriyadi.png')
        ->and($pimpinan['rektor']['image_zoom'])->toBe(1.0)
        ->and($pimpinan['sekjen']['image_zoom'])->toBe(1.0);

    foreach ($pimpinan as $item) {
        expect(public_path(ltrim($item['image'], '/')))->toBeFile();
    }
});

it('seeds the green Teacher of Champions card while keeping the official poster', function () {
    $payload = $this->service->toArray(Kegiatan::query()->where('slug', 'teacher-of-champions')->firstOrFail());

    expect($payload['image'])->toBe('/images/event-teacher-card-hijau.jpg')
        ->and($payload['image_sm'])->toBe('/images/event-teacher-card-hijau-sm.jpg')
        ->and($payload['hero_image'])->toBe('/images/teacher-of-champions-poster.jpg');
});
