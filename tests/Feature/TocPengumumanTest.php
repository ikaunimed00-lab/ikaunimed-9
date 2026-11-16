<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocAksesLog;
use App\Services\TocAksesMonitorService;
use App\Support\TocNomorPendaftaran;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['toc.pengumuman_dibuka' => true]);
    config(['toc.link_grup_wa_finalis' => 'https://chat.whatsapp.com/TEST-FINALIS-ONLY']);
    Cache::flush();
});

it('normalizes registration numbers loosely', function (string $input, ?string $expected) {
    expect(TocNomorPendaftaran::normalize($input))->toBe($expected);
})->with([
    'canonical' => ['TOC-2026-0071', 'TOC-2026-0071'],
    'lowercase' => ['toc20260071', 'TOC-2026-0071'],
    'spaces' => ['TOC 2026 0071', 'TOC-2026-0071'],
    'padded' => ['0071', 'TOC-2026-0071'],
    'short' => ['71', 'TOC-2026-0071'],
    'eighteen' => ['18', 'TOC-2026-0018'],
    'out of range' => ['9999', null],
    'invalid' => ['ABC', null],
]);

it('hides results when pengumuman is closed', function () {
    config(['toc.pengumuman_dibuka' => false]);

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nama' => 'Guru Lulus Rahasia',
    ]);

    $this->post(route('toc.pengumuman.check'), [
        'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
    ])->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertInertia(fn (Assert $page) => $page
            ->where('pengumumanDibuka', false)
            ->where('result', null)
        )
        ->assertDontSee('https://chat.whatsapp.com/TEST-FINALIS-ONLY', false)
        ->assertDontSee('Guru Lulus Rahasia', false);
});

it('shows congratulations and group link for passing participants', function () {
    PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nomor_pendaftaran' => 'TOC-2026-0018',
        'nama' => 'Siti Aminah',
    ]);

    $response = $this->post(route('toc.pengumuman.check'), [
        'nomor_pendaftaran' => '18',
    ]);

    $response->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Pengumuman')
            ->where('result.status', 'lulus')
            ->where('result.nama', 'Siti Aminah')
            ->where('result.linkGrupWa', 'https://chat.whatsapp.com/TEST-FINALIS-ONLY')
            ->has('result.jadwal.tmFinal')
            ->has('result.berkasUrl')
            ->missing('result.whatsapp')
            ->missing('result.email')
        );

    expect($response->headers->get('Location'))->toBeNull();

    expect(TocAksesLog::query()->where('hasil', 'berhasil')->count())->toBe(1);
});

it('shows polite rejection without group link for non-passing participants', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->tidakLulus()->create([
        'nama' => 'Budi Santoso',
    ]);

    $response = $this->post(route('toc.pengumuman.check'), [
        'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
    ]);

    $response->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertInertia(fn (Assert $page) => $page
            ->where('result.status', 'tidak_lulus')
            ->where('result.nama', 'Budi Santoso')
            ->missing('result.linkGrupWa')
            ->missing('result.jadwal')
            ->missing('result.berkasUrl')
        )
        ->assertDontSee('https://chat.whatsapp.com/TEST-FINALIS-ONLY', false);

    expect($response->headers->get('Location'))->toBeNull();
});

it('sends no-store header on the empty pengumuman page', function () {
    $this->get(route('toc.pengumuman'))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('uses a back link to /toc instead of checking another number', function () {
    $source = file_get_contents(resource_path('js/Pages/TeacherOfChampions/Pengumuman.tsx'));

    expect($source)
        ->not->toContain('Cek nomor lain')
        ->toContain('Kembali ke halaman Teacher of Champions')
        ->toContain('href="/toc"');
});

it('serves the Teacher of Champions landing at /toc', function () {
    $this->get('/toc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('TeacherOfChampions/Landing'));
});

it('uses the same generic error for missing numbers', function () {
    $this->from(route('toc.pengumuman'))
        ->post(route('toc.pengumuman.check'), [
            'nomor_pendaftaran' => '9999',
        ])
        ->assertRedirect(route('toc.pengumuman'))
        ->assertSessionHasErrors(['credentials' => config('toc.login.generic_error')]);
});

it('blocks scanning after many distinct numbers from one IP', function () {
    config(['toc.login.scan_distinct_threshold' => 3]);

    PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-0001']);
    PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-0002']);
    PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-0003']);
    PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-0004']);

    foreach (['1', '2', '3', '4'] as $seq) {
        $this->post(route('toc.pengumuman.check'), [
            'nomor_pendaftaran' => $seq,
        ]);
    }

    $this->post(route('toc.pengumuman.check'), [
        'nomor_pendaftaran' => '1',
    ])->assertStatus(429);

    expect(app(TocAksesMonitorService::class)->isIpBlocked('127.0.0.1'))->toBeTrue();
});
