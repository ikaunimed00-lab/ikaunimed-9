<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config([
        'toc.pengumuman_dibuka' => true,
        'toc.batas_unggah' => '2026-11-18 23:59:59',
        'toc.batas_unggah_ulang' => '2026-11-18 23:59:59',
    ]);
    Cache::flush();
    Storage::fake('local');
    Carbon::setTestNow(Carbon::parse('2026-11-16 10:00', 'Asia/Jakarta'));
});

afterEach(function () {
    Carbon::setTestNow();
});

function loginBerkas(PendaftarTeacherOfChampions $peserta)
{
    return test()->from(route('toc.berkas'))->post(route('toc.berkas.login'), [
        'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
    ]);
}

function makeImage(int $width, int $height, string $name = 'foto.jpg'): UploadedFile
{
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-img-', true).'.jpg';
    $image = imagecreatetruecolor($width, $height);
    imagejpeg($image, $path, 90);
    imagedestroy($image);

    return new UploadedFile($path, $name, 'image/jpeg', null, true);
}

it('sends no-store cache headers on berkas login and formulir pages', function () {
    $this->get(route('toc.berkas'))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect(route('toc.berkas.formulir'));

    $this->get(route('toc.berkas.formulir'))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

it('rejects non-passing participants from berkas login with the generic message', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->tidakLulus()->create();

    loginBerkas($peserta)
        ->assertRedirect(route('toc.berkas'))
        ->assertSessionHasErrors(['credentials' => config('toc.login.generic_error')]);

    $this->get(route('toc.berkas.formulir'))->assertRedirect(route('toc.berkas'));
});

it('rejects berkas access when pengumuman is closed', function () {
    config(['toc.pengumuman_dibuka' => false]);

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();

    loginBerkas($peserta)->assertRedirect()->assertSessionHasErrors('credentials');
});

it('lets passing participants open the upload form with a single consent checkbox on foto', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nama' => 'Finalis Aktif',
    ]);

    loginBerkas($peserta)->assertRedirect(route('toc.berkas.formulir'));

    $this->get(route('toc.berkas.formulir'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/BerkasFormulir')
            ->where('peserta.nama', 'Finalis Aktif')
            ->where('terkunci', false)
            ->has('berkas', 3)
            ->where('berkas.0.butuhPersetujuan', true)
            ->where('berkas.1.butuhPersetujuan', false)
            ->where('berkas.2.butuhPersetujuan', false)
        );
});

it('forces a full reload after berkas login so the csrf token stays fresh', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();

    $this->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
        ->from(route('toc.berkas'))
        ->post(route('toc.berkas.login'), [
            'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('toc.berkas.formulir'));
});

it('rejects oversized uploads with a friendly Indonesian size message', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-big-', true).'.pdf';
    file_put_contents($path, str_repeat('a', 3072 * 1024 + 10));

    $this->post(route('toc.berkas.unggah', 'pakta_integritas'), [
        'file' => new UploadedFile($path, 'pakta-besar.pdf', 'application/pdf', null, true),
    ])->assertRedirect()
        ->assertSessionHasErrors(['file' => 'Ukuran file melebihi 3 MB.']);
});

it('rejects server-rejected uploads without English error text', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-ini-', true).'.jpg';
    file_put_contents($path, str_repeat('b', 100));

    $file = new UploadedFile($path, 'foto.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => $file,
        'setuju_publikasi' => '1',
    ])->assertRedirect()
        ->assertSessionHasErrors('file');

    $errors = session('errors')->getBag('default')->get('file');
    expect($errors[0] ?? '')
        ->toContain('batas server')
        ->not->toContain('failed to upload');
});

it('rejects undersized photos and stores valid uploads privately with ip metadata', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();

    loginBerkas($peserta)->assertRedirect();

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(800, 1000, 'kecil.jpg'),
        'setuju_publikasi' => '1',
    ])->assertRedirect()->assertSessionHasErrors('file');

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(1080, 1350, 'baik.jpg'),
        'setuju_publikasi' => '1',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $berkas = TocBerkas::query()->sole();

    expect($berkas->jenis)->toBe('foto')
        ->and($berkas->status)->toBe('menunggu')
        ->and($berkas->uploaded_ip)->not->toBeNull()
        ->and(Storage::disk('local')->exists($berkas->path))->toBeTrue();
});

it('rejects replacing an accepted berkas', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $path = 'toc/berkas/'.$peserta->nomor_pendaftaran.'/old.jpg';
    Storage::disk('local')->put($path, 'old');

    TocBerkas::query()->create([
        'pendaftar_id' => $peserta->id,
        'jenis' => TocBerkas::JENIS_FOTO,
        'path' => $path,
        'nama_asli' => 'old.jpg',
        'mime' => 'image/jpeg',
        'ukuran' => 10,
        'status' => TocBerkas::STATUS_DITERIMA,
        'uploaded_at' => now(),
    ]);

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(1080, 1350, 'baru.jpg'),
        'setuju_publikasi' => '1',
    ])->assertRedirect()->assertSessionHasErrors('file');

    expect(TocBerkas::query()->sole()->nama_asli)->toBe('old.jpg');
});

it('replaces a waiting file and deletes the old one from disk', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(1080, 1350, 'satu.jpg'),
        'setuju_publikasi' => '1',
    ])->assertSessionHasNoErrors();

    $oldPath = TocBerkas::query()->sole()->path;

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(1200, 1500, 'dua.jpg'),
        'setuju_publikasi' => '1',
    ])->assertSessionHasNoErrors();

    expect(TocBerkas::query()->count())->toBe(1)
        ->and(Storage::disk('local')->exists($oldPath))->toBeFalse()
        ->and(TocBerkas::query()->sole()->nama_asli)->toBe('dua.jpg');
});

it('rejects uploads after the deadline on the server', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-19 00:00:01', 'Asia/Jakarta'));

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $this->post(route('toc.berkas.unggah', 'foto'), [
        'file' => makeImage(1080, 1350),
        'setuju_publikasi' => '1',
    ])->assertRedirect()->assertSessionHasErrors('file');
});

it('keeps the formulir countdown and unlock state until 18 November 2026', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-18 23:59:00', 'Asia/Jakarta'));

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    loginBerkas($peserta)->assertRedirect();

    $this->get(route('toc.berkas.formulir'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('terkunci', false)
            ->where('batasUnggah', Carbon::parse('2026-11-18 23:59:59', 'Asia/Jakarta')->toIso8601String()));

    Carbon::setTestNow(Carbon::parse('2026-11-19 00:00:00', 'Asia/Jakarta'));

    $this->get(route('toc.berkas.formulir'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('terkunci', true));
});

it('serves public templates', function () {
    $this->get(route('toc.berkas.template', 'pakta-integritas-pdf'))
        ->assertOk();
});
