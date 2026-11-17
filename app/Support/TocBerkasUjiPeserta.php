<?php

namespace App\Support;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasRejectionMailLog;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Buat dan hapus peserta uji berkas di luar 514 finalis.
 * Jangan pernah memakai atau mengubah peserta asli TOC-2026-xxxx yang lulus.
 */
class TocBerkasUjiPeserta
{
    public const NOMOR_PREFIX = 'TOC-UJI-';

    public const NAMA_MARKER = 'TOC-UJI-BERKAS';

    /**
     * Nomor uji: TOC-UJI-0001 … TOC-UJI-9999 (bukan format finalis TOC-2026-xxxx).
     */
    public static function makeNomor(?int $sequence = null): string
    {
        $sequence ??= random_int(1, 9999);

        if ($sequence < 1 || $sequence > 9999) {
            throw new InvalidArgumentException('Sequence nomor uji harus 1..9999.');
        }

        return sprintf('%s%04d', self::NOMOR_PREFIX, $sequence);
    }

    public static function isUjiNomor(string $nomor): bool
    {
        return (bool) preg_match('/^TOC-UJI-\d{4}$/', $nomor);
    }

    public static function isFinalistNomor(string $nomor): bool
    {
        return (bool) preg_match('/^TOC-2026-\d{4}$/', $nomor);
    }

    /**
     * Tolak bila nomor/peserta adalah finalis asli (lulus penyisihan).
     */
    public static function assertNotRealFinalist(PendaftarTeacherOfChampions|string $pesertaOrNomor): void
    {
        if ($pesertaOrNomor instanceof PendaftarTeacherOfChampions) {
            $peserta = $pesertaOrNomor;
            $nomor = (string) $peserta->nomor_pendaftaran;
        } else {
            $nomor = $pesertaOrNomor;
            $peserta = PendaftarTeacherOfChampions::query()
                ->where('nomor_pendaftaran', $nomor)
                ->first();
        }

        if ($peserta !== null && $peserta->lulus_penyisihan === true && self::isFinalistNomor($nomor)) {
            throw new RuntimeException(
                "Dilarang memakai peserta finalis asli ({$nomor}) untuk uji. Gunakan nomor TOC-UJI-xxxx."
            );
        }

        if ($peserta === null && self::isFinalistNomor($nomor)) {
            throw new RuntimeException(
                "Dilarang memakai nomor finalis asli ({$nomor}) untuk uji. Gunakan nomor TOC-UJI-xxxx."
            );
        }
    }

    /**
     * Buat peserta uji baru. Tidak mengisi ulang / mengubah finalis.
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function create(array $overrides = []): PendaftarTeacherOfChampions
    {
        $requestedNomor = $overrides['nomor_pendaftaran'] ?? null;

        if (is_string($requestedNomor) && $requestedNomor !== '' && ! self::isUjiNomor($requestedNomor)) {
            throw new InvalidArgumentException('Peserta uji harus memakai nomor TOC-UJI-xxxx.');
        }

        $attempts = 0;
        $nomor = is_string($requestedNomor) && $requestedNomor !== ''
            ? $requestedNomor
            : self::makeNomor();

        while (PendaftarTeacherOfChampions::query()->where('nomor_pendaftaran', $nomor)->exists()) {
            if (is_string($requestedNomor) && $requestedNomor !== '') {
                throw new RuntimeException("Nomor uji {$nomor} sudah dipakai.");
            }

            $attempts++;
            if ($attempts > 50) {
                throw new RuntimeException('Gagal menemukan nomor uji unik.');
            }

            $nomor = self::makeNomor();
        }

        self::assertNotRealFinalist($nomor);

        $attributes = array_merge([
            'nomor_pendaftaran' => $nomor,
            'nama' => self::NAMA_MARKER,
            'asal_instansi' => 'Uji',
            'whatsapp' => '629'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'email' => strtolower($nomor).'@example.test',
            'provinsi' => 'Sumatera Utara',
            'kabupaten_kota' => 'Kota Medan',
            'lama_mengabdi_tahun' => 1,
            'instagram' => '@uji-'.strtolower(str_replace('-', '', $nomor)),
            'bidang_studi' => 'Uji',
            'asal_universitas' => 'Uji',
            'jenjang' => 'SMA',
            'status_kepegawaian' => 'ASN',
            'persetujuan_data' => true,
            'status' => 'baru',
            'lulus_penyisihan' => true,
        ], $overrides, [
            'nomor_pendaftaran' => $nomor,
            'nama' => is_string($overrides['nama'] ?? null) ? $overrides['nama'] : self::NAMA_MARKER,
        ]);

        return PendaftarTeacherOfChampions::query()->create($attributes);
    }

    /**
     * Hapus peserta uji beserta berkas fisik dan log penolakan.
     */
    public static function destroy(PendaftarTeacherOfChampions $peserta): void
    {
        if (! self::isUjiNomor((string) $peserta->nomor_pendaftaran)) {
            throw new RuntimeException('Hanya peserta uji (TOC-UJI-*) yang boleh dihapus lewat helper ini.');
        }

        self::assertNotRealFinalist($peserta);

        $disk = Storage::disk(config('toc.berkas.disk'));
        $berkas = TocBerkas::query()->where('pendaftar_id', $peserta->id)->get();

        foreach ($berkas as $item) {
            TocBerkasRejectionMailLog::query()->where('berkas_id', $item->id)->delete();
            if (is_string($item->path) && $item->path !== '') {
                $disk->delete($item->path);
            }
            $item->delete();
        }

        TocBerkasRejectionMailLog::query()->where('pendaftar_id', $peserta->id)->delete();
        $disk->deleteDirectory('toc/berkas/'.$peserta->nomor_pendaftaran);
        $peserta->delete();
    }
}
