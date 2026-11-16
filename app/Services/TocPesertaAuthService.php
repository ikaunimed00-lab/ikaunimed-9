<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use App\Support\TocNomorPendaftaran;
use Illuminate\Http\Request;

class TocPesertaAuthService
{
    public const GENERIC_ERROR_KEY = 'credentials';

    public function __construct(
        protected TocAksesMonitorService $monitor,
    ) {}

    /**
     * @return array{ok: true, pendaftar: PendaftarTeacherOfChampions}|array{ok: false, error: string, blocked?: bool}
     */
    public function attempt(Request $request, string $nomorInput, bool $requireLulus = false, string $sumber = 'pengumuman'): array
    {
        if ($this->monitor->isIpBlocked($request->ip())) {
            return [
                'ok' => false,
                'error' => (string) config('toc.login.scan_block_message'),
                'blocked' => true,
            ];
        }

        $nomor = TocNomorPendaftaran::normalize($nomorInput);

        if ($nomor === null) {
            $this->monitor->record($request, $nomorInput, 'gagal', $sumber);

            return $this->fail();
        }

        $this->monitor->trackDistinctNomor($request->ip(), $nomor);

        if ($this->monitor->shouldBlockForScanning($request->ip())) {
            $this->monitor->blockIp($request->ip(), 'scan');
            $this->monitor->record($request, $nomor, 'gagal', $sumber);

            return [
                'ok' => false,
                'error' => (string) config('toc.login.scan_block_message'),
                'blocked' => true,
            ];
        }

        $pendaftar = PendaftarTeacherOfChampions::query()
            ->where('nomor_pendaftaran', $nomor)
            ->first([
                'id',
                'nomor_pendaftaran',
                'nama',
                'lulus_penyisihan',
                'bidang_studi',
                'setuju_publikasi_at',
            ]);

        if ($pendaftar === null) {
            $this->monitor->record($request, $nomor, 'gagal', $sumber);

            return $this->fail();
        }

        if ($requireLulus && $pendaftar->lulus_penyisihan !== true) {
            $this->monitor->record($request, $nomor, 'gagal', $sumber);

            return $this->fail();
        }

        $this->monitor->record($request, $nomor, 'berhasil', $sumber);

        return [
            'ok' => true,
            'pendaftar' => $pendaftar,
        ];
    }

    /**
     * @return array{ok: false, error: string}
     */
    private function fail(): array
    {
        return [
            'ok' => false,
            'error' => (string) config('toc.login.generic_error'),
        ];
    }
}
