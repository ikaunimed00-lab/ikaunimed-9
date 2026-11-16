<?php

use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Support\Facades\File;

it('imports lulus flags idempotently when counts match expectations', function () {
    config([
        'toc.expected_import.lulus' => 2,
        'toc.expected_import.tidak_lulus' => 3,
        'toc.expected_import.total' => 5,
    ]);

    $lulus = collect([
        PendaftarTeacherOfChampions::factory()->create(['nomor_pendaftaran' => 'TOC-2026-0001']),
        PendaftarTeacherOfChampions::factory()->create(['nomor_pendaftaran' => 'TOC-2026-0002']),
    ]);
    PendaftarTeacherOfChampions::factory()->create(['nomor_pendaftaran' => 'TOC-2026-0003']);
    PendaftarTeacherOfChampions::factory()->create(['nomor_pendaftaran' => 'TOC-2026-0004']);
    PendaftarTeacherOfChampions::factory()->create(['nomor_pendaftaran' => 'TOC-2026-0005']);

    $path = storage_path('app/testing-toc-lulus.csv');
    File::ensureDirectoryExists(dirname($path));
    $rows = ['nomor_pendaftaran,status'];
    foreach ($lulus as $item) {
        $rows[] = $item->nomor_pendaftaran.',lulus';
    }
    File::put($path, implode("\n", $rows)."\n");

    $this->artisan('toc:import-lulus', ['file' => $path, '--dry-run' => true])
        ->assertSuccessful();

    expect(PendaftarTeacherOfChampions::query()->whereNotNull('lulus_penyisihan')->count())->toBe(0);

    $this->artisan('toc:import-lulus', ['file' => $path])
        ->assertSuccessful();

    expect(PendaftarTeacherOfChampions::query()->where('lulus_penyisihan', true)->count())->toBe(2)
        ->and(PendaftarTeacherOfChampions::query()->where('lulus_penyisihan', false)->count())->toBe(3);

    $this->artisan('toc:import-lulus', ['file' => $path])
        ->assertSuccessful();

    expect(PendaftarTeacherOfChampions::query()->where('lulus_penyisihan', true)->count())->toBe(2);

    File::delete($path);
});

it('aborts before writing when expected counts do not match', function () {
    config([
        'toc.expected_import.lulus' => 514,
        'toc.expected_import.tidak_lulus' => 3604,
        'toc.expected_import.total' => 4118,
    ]);

    $lulus = PendaftarTeacherOfChampions::factory()->create();
    PendaftarTeacherOfChampions::factory()->count(2)->create();

    $path = storage_path('app/testing-toc-lulus-mismatch.csv');
    File::put($path, "nomor_pendaftaran,status\n{$lulus->nomor_pendaftaran},lulus\n");

    $this->artisan('toc:import-lulus', ['file' => $path])
        ->assertFailed();

    expect(PendaftarTeacherOfChampions::query()->whereNotNull('lulus_penyisihan')->count())->toBe(0);

    File::delete($path);
});
