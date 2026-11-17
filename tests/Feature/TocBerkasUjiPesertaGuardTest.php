<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Support\TocBerkasUjiPeserta;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('creates uji participants outside the finalist nomor space', function () {
    $peserta = TocBerkasUjiPeserta::create();

    expect($peserta->nomor_pendaftaran)->toMatch('/^TOC-UJI-\d{4}$/')
        ->and(TocBerkasUjiPeserta::isUjiNomor($peserta->nomor_pendaftaran))->toBeTrue()
        ->and(TocBerkasUjiPeserta::isFinalistNomor($peserta->nomor_pendaftaran))->toBeFalse()
        ->and($peserta->nama)->toBe(TocBerkasUjiPeserta::NAMA_MARKER)
        ->and($peserta->lulus_penyisihan)->toBeTrue();

    TocBerkasUjiPeserta::destroy($peserta);

    expect(PendaftarTeacherOfChampions::query()->whereKey($peserta->id)->exists())->toBeFalse();
});

it('refuses to treat a real finalist nomor as a test subject', function () {
    $finalis = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nomor_pendaftaran' => 'TOC-2026-0066',
        'nama' => 'MUHAMMAD IDRIS',
    ]);

    expect(fn () => TocBerkasUjiPeserta::assertNotRealFinalist($finalis))
        ->toThrow(RuntimeException::class, 'TOC-2026-0066');

    expect(fn () => TocBerkasUjiPeserta::assertNotRealFinalist('TOC-2026-0066'))
        ->toThrow(RuntimeException::class, 'TOC-2026-0066');

    expect(fn () => TocBerkasUjiPeserta::create(['nomor_pendaftaran' => 'TOC-2026-0066']))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses to destroy a real finalist even if the name is renamed to a smoke marker', function () {
    $finalis = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nomor_pendaftaran' => 'TOC-2026-0066',
        'nama' => TocBerkasUjiPeserta::NAMA_MARKER,
    ]);

    expect(fn () => TocBerkasUjiPeserta::destroy($finalis))
        ->toThrow(RuntimeException::class);

    expect($finalis->fresh())->not->toBeNull()
        ->and($finalis->fresh()->nomor_pendaftaran)->toBe('TOC-2026-0066');
});

it('fails when a production-style smoke reuse would overwrite a finalist', function () {
    $finalis = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nomor_pendaftaran' => 'TOC-2026-0066',
        'nama' => 'MUHAMMAD IDRIS',
        'email' => 'peserta-asli@example.org',
    ]);

    $dangerousReuse = function () use ($finalis): void {
        $existing = PendaftarTeacherOfChampions::query()
            ->where(function ($q): void {
                $q->where('nama', 'like', 'TOC-SMOKE%')
                    ->orWhere('email', 'like', '%@example.test')
                    ->orWhere('email', 'like', 'toc-smoke%');
            })
            ->orderByDesc('id')
            ->first();

        // Simulasikan bug lama: bila tidak ketemu, salah ambil finalis.
        $target = $existing ?? $finalis;
        TocBerkasUjiPeserta::assertNotRealFinalist($target);

        $target->forceFill([
            'nama' => 'TOC-SMOKE-BROWSER-VERIFY',
            'email' => 'toc-smoke-browser@example.test',
        ])->save();
    };

    expect($dangerousReuse)->toThrow(RuntimeException::class);
    expect($finalis->fresh()->nama)->toBe('MUHAMMAD IDRIS');
});
