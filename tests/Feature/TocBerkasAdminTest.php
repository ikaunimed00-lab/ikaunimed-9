<?php

use App\Filament\Resources\TocBerkasFinalis\Pages\ManageTocBerkasFinalis;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\User;
use App\Services\TocBerkasAdminService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('local');
});

it('lets admins open the berkas finalis panel', function () {
    PendaftarTeacherOfChampions::factory()->lulus()->create(['nama' => 'Finalis Panel']);
    PendaftarTeacherOfChampions::factory()->tidakLulus()->create(['nama' => 'Bukan Finalis']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin/berkas-finalis-toc')
        ->assertOk()
        ->assertSee('Finalis Panel')
        ->assertDontSee('Bukan Finalis');
});

it('hides the panel from editors', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get('/admin/berkas-finalis-toc')
        ->assertForbidden();
});

it('accepts and rejects berkas through admin routes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    $path = 'toc/berkas/'.$peserta->nomor_pendaftaran.'/foto.jpg';
    Storage::disk('local')->put($path, 'fake-image');

    $berkas = TocBerkas::query()->create([
        'pendaftar_id' => $peserta->id,
        'jenis' => TocBerkas::JENIS_FOTO,
        'path' => $path,
        'nama_asli' => 'foto.jpg',
        'mime' => 'image/jpeg',
        'ukuran' => 100,
        'status' => TocBerkas::STATUS_MENUNGGU,
        'uploaded_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.toc.berkas.terima', $berkas))
        ->assertRedirect();

    expect($berkas->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA);

    $this->actingAs($admin)
        ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => 'Resolusi kurang tajam'])
        ->assertRedirect();

    expect($berkas->fresh()->status)->toBe(TocBerkas::STATUS_DITOLAK)
        ->and($berkas->fresh()->catatan)->toBe('Resolusi kurang tajam');

    $this->actingAs($admin)
        ->get(route('admin.toc.berkas.download', $berkas))
        ->assertOk();
});

it('exports csv status and zip of accepted photos', function () {
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nama' => 'Guru Foto',
        'nomor_pendaftaran' => 'TOC-2026-0100',
    ]);
    $path = 'toc/berkas/TOC-2026-0100/foto.jpg';
    Storage::disk('local')->put($path, 'jpeg-bytes');

    TocBerkas::query()->create([
        'pendaftar_id' => $peserta->id,
        'jenis' => TocBerkas::JENIS_FOTO,
        'path' => $path,
        'nama_asli' => 'foto.jpg',
        'mime' => 'image/jpeg',
        'ukuran' => 10,
        'status' => TocBerkas::STATUS_DITERIMA,
        'uploaded_at' => now(),
    ]);

    $collection = PendaftarTeacherOfChampions::query()->whereKey($peserta->id)->with('berkas')->get();
    $service = app(TocBerkasAdminService::class);

    $csv = $service->exportCsv($collection);
    expect($csv->headers->get('content-disposition'))->toContain('status-berkas-toc-');

    $zip = $service->downloadAcceptedPhotosZip($collection);
    expect($zip->headers->get('content-disposition'))->toContain('foto-finalis-toc-diterima.zip');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
    Filament::setCurrentPanel('admin');

    Livewire::test(ManageTocBerkasFinalis::class)
        ->assertCanSeeTableRecords([$peserta]);
});
