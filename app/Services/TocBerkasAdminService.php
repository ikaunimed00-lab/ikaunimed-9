<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class TocBerkasAdminService
{
    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function downloadAcceptedPhotosZip(Collection $pendaftar): StreamedResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'toc-foto-');
        $zipPath = $tmp.'.zip';
        @unlink($tmp);

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $disk = Storage::disk(config('toc.berkas.disk'));
        $added = 0;

        foreach ($pendaftar as $peserta) {
            /** @var TocBerkas|null $foto */
            $foto = $peserta->berkas
                ->first(fn (TocBerkas $b) => $b->jenis === TocBerkas::JENIS_FOTO && $b->status === TocBerkas::STATUS_DITERIMA);

            if (! $foto || ! $disk->exists($foto->path)) {
                continue;
            }

            $ext = pathinfo($foto->nama_asli, PATHINFO_EXTENSION) ?: pathinfo($foto->path, PATHINFO_EXTENSION) ?: 'jpg';
            $safeName = $this->sanitizeFilename($peserta->nama);
            $entry = sprintf('%s_%s.%s', $peserta->nomor_pendaftaran, $safeName, strtolower($ext));
            $zip->addFromString($entry, $disk->get($foto->path));
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(404, 'Tidak ada foto berstatus diterima.');
        }

        return response()->streamDownload(function () use ($zipPath): void {
            $stream = fopen($zipPath, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
            @unlink($zipPath);
        }, 'foto-finalis-toc-diterima.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function exportCsv(Collection $pendaftar): StreamedResponse
    {
        $filename = 'status-berkas-toc-'.now(config('toc.timezone'))->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($pendaftar): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'nomor_pendaftaran',
                'nama',
                'bidang_studi',
                'status_foto',
                'status_pakta',
                'status_surat',
                'status_keseluruhan',
                'unggah_terakhir',
                'setuju_publikasi',
            ]);

            foreach ($pendaftar as $peserta) {
                $byJenis = $peserta->berkas->keyBy('jenis');
                $foto = $byJenis->get(TocBerkas::JENIS_FOTO);
                $pakta = $byJenis->get(TocBerkas::JENIS_PAKTA);
                $surat = $byJenis->get(TocBerkas::JENIS_SURAT);
                $latest = $peserta->berkas->max('uploaded_at');

                fputcsv($out, [
                    $peserta->nomor_pendaftaran,
                    $peserta->nama,
                    $peserta->bidang_studi,
                    $foto?->status ?? 'belum',
                    $pakta?->status ?? 'belum',
                    $surat?->status ?? 'belum',
                    $this->overallStatus($foto, $pakta, $surat),
                    $latest ? (string) $latest : '',
                    $peserta->setuju_publikasi_at ? 'ya' : 'tidak',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function overallStatus(?TocBerkas $foto, ?TocBerkas $pakta, ?TocBerkas $surat): string
    {
        $items = [$foto, $pakta, $surat];

        if (collect($items)->contains(fn (?TocBerkas $b) => $b?->status === TocBerkas::STATUS_DITOLAK)) {
            return 'ditolak';
        }

        if (collect($items)->every(fn (?TocBerkas $b) => $b !== null)) {
            if (collect($items)->every(fn (?TocBerkas $b) => $b?->status === TocBerkas::STATUS_DITERIMA)) {
                return 'lengkap_diterima';
            }

            return 'lengkap';
        }

        if (collect($items)->contains(fn (?TocBerkas $b) => $b?->status === TocBerkas::STATUS_MENUNGGU)) {
            return 'menunggu';
        }

        return 'belum_lengkap';
    }

    private function sanitizeFilename(string $name): string
    {
        $safe = Str::of($name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '-')
            ->trim('-')
            ->limit(60, '')
            ->toString();

        return $safe !== '' ? $safe : 'Peserta';
    }
}
