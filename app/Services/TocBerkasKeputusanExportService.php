<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class TocBerkasKeputusanExportService
{
    public const XLSX_NAME = 'keputusan-berkas.xlsx';

    public const SHEET_KEPUTUSAN = 'Keputusan';

    public const SHEET_PETUNJUK = 'Petunjuk';

    /**
     * Awalan kolom Excel per jenis berkas.
     *
     * @var array<string, string>
     */
    public const COLUMN_PREFIX = [
        TocBerkas::JENIS_FOTO => 'foto',
        TocBerkas::JENIS_PAKTA => 'pakta',
        TocBerkas::JENIS_SURAT => 'surat',
    ];

    /**
     * Nama file di dalam folder peserta.
     *
     * @var array<string, string>
     */
    public const ENTRY_BASENAME = [
        TocBerkas::JENIS_FOTO => 'foto',
        TocBerkas::JENIS_PAKTA => 'pakta-integritas',
        TocBerkas::JENIS_SURAT => 'surat-keterangan',
    ];

    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        $headers = ['nomor_pendaftaran', 'nama', 'bidang_studi'];

        foreach (self::COLUMN_PREFIX as $prefix) {
            array_push($headers, "{$prefix}_status_saat_ini", "{$prefix}_keputusan", "{$prefix}_alasan");
        }

        foreach (self::COLUMN_PREFIX as $prefix) {
            array_push($headers, "_{$prefix}_id", "_{$prefix}_ver");
        }

        return $headers;
    }

    public function assertWithinLimit(int $count): void
    {
        $max = (int) config('toc.berkas.export_max_peserta', 600);

        if ($count > $max) {
            throw ValidationException::withMessages([
                'peserta' => "Maksimal {$max} peserta per unduhan (dipilih {$count}). Persempit filter atau pilih lebih sedikit baris.",
            ]);
        }
    }

    public function partSize(): int
    {
        return max(1, (int) config('toc.berkas.export_chunk_size', 50));
    }

    /**
     * @param  list<int>  $ids
     * @return list<list<int>>
     */
    public function partitionIds(array $ids): array
    {
        $unique = array_values(array_unique(array_map('intval', $ids)));

        return array_values(array_chunk($unique, $this->partSize()));
    }

    /**
     * Seluruh finalis, urut nomor pendaftaran. Urutan yang sama dengan ZIP penuh pada sortir bawaan.
     *
     * @return list<int>
     */
    public function finalistIdsInNomorOrder(): array
    {
        return PendaftarTeacherOfChampions::query()
            ->where('lulus_penyisihan', true)
            ->orderBy('nomor_pendaftaran')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function bagianFilterOptions(): array
    {
        $chunks = $this->partitionIds($this->finalistIdsInNomorOrder());
        $options = [];
        $cursor = 1;

        foreach ($chunks as $index => $chunk) {
            $count = count($chunk);
            $dari = $cursor;
            $sampai = $cursor + $count - 1;
            $bagian = $index + 1;
            $options[(string) $bagian] = "Bagian {$bagian} ({$dari}-{$sampai})";
            $cursor = $sampai + 1;
        }

        return $options;
    }

    /**
     * @return list<int>
     */
    public function idsForBagian(int $bagian): array
    {
        if ($bagian < 1) {
            return [];
        }

        $chunks = $this->partitionIds($this->finalistIdsInNomorOrder());

        return $chunks[$bagian - 1] ?? [];
    }

    /**
     * @param  list<int>  $ids
     * @return array{token: string, total_peserta: int, total_bagian: int, parts: list<array{bagian: int, dari: int, sampai: int, jumlah: int, label: string, url: string}>}
     */
    public function rememberSelection(int $userId, array $ids): array
    {
        $chunks = $this->partitionIds($ids);
        $flat = array_merge(...($chunks ?: [[]]));
        $this->assertWithinLimit(count($flat));

        if ($flat === []) {
            throw ValidationException::withMessages([
                'peserta' => 'Tidak ada peserta untuk diekspor.',
            ]);
        }

        $token = (string) Str::uuid();
        $ttl = max(5, (int) config('toc.berkas.export_selection_ttl_minutes', 120));

        Cache::put($this->selectionCacheKey($token), [
            'user_id' => $userId,
            'ids' => $flat,
            'part_size' => $this->partSize(),
        ], now()->addMinutes($ttl));

        return $this->selectionManifest($token, $flat);
    }

    /**
     * @return array{user_id: int, ids: list<int>, part_size: int}|null
     */
    public function selection(string $token): ?array
    {
        $payload = Cache::get($this->selectionCacheKey($token));

        if (! is_array($payload) || ! isset($payload['user_id'], $payload['ids']) || ! is_array($payload['ids'])) {
            return null;
        }

        return [
            'user_id' => (int) $payload['user_id'],
            'ids' => array_values(array_map('intval', $payload['ids'])),
            'part_size' => max(1, (int) ($payload['part_size'] ?? $this->partSize())),
        ];
    }

    /**
     * @param  list<int>  $ids
     * @return array{token: string, total_peserta: int, total_bagian: int, parts: list<array{bagian: int, dari: int, sampai: int, jumlah: int, label: string, url: string}>}
     */
    public function selectionManifest(string $token, array $ids): array
    {
        $chunks = $this->partitionIds($ids);
        $total = count($chunks);
        $parts = [];
        $cursor = 1;

        foreach ($chunks as $index => $chunk) {
            $count = count($chunk);
            $dari = $cursor;
            $sampai = $cursor + $count - 1;
            $bagian = $index + 1;
            $parts[] = [
                'bagian' => $bagian,
                'dari' => $dari,
                'sampai' => $sampai,
                'jumlah' => $count,
                'label' => "Bagian {$bagian} (peserta {$dari}-{$sampai})",
                'url' => route('admin.toc.berkas.exports.langsung', [
                    'token' => $token,
                    'bagian' => $bagian,
                ]),
            ];
            $cursor = $sampai + 1;
        }

        return [
            'token' => $token,
            'total_peserta' => count($ids),
            'total_bagian' => $total,
            'parts' => $parts,
        ];
    }

    public function zipFilename(int $bagian, int $totalBagian, ?\DateTimeInterface $when = null): string
    {
        $tanggal = ($when ?? now(config('toc.timezone')))->format('Ymd');

        return "berkas-finalis-bagian-{$bagian}-dari-{$totalBagian}-{$tanggal}.zip";
    }

    /**
     * Streaming ZIP langsung ke klien (ZipStream); berkas dibaca dari disk, bukan dimuat ke memori.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function streamZip(Collection $pendaftar, string $downloadName): StreamedResponse
    {
        $this->assertWithinLimit($pendaftar->count());
        $pendaftar->each(fn (PendaftarTeacherOfChampions $peserta) => $peserta->loadMissing('berkas'));

        $timeLimit = max(60, (int) config('toc.berkas.export_download_time_limit', 300));
        if (function_exists('set_time_limit')) {
            @set_time_limit($timeLimit);
        }

        return response()->streamDownload(function () use ($pendaftar): void {
            $disk = Storage::disk(config('toc.berkas.disk'));
            $tmp = tempnam(sys_get_temp_dir(), 'toc-xlsx-');
            $xlsxPath = $tmp.'.xlsx';
            @unlink($tmp);

            $zip = new ZipStream(
                sendHttpHeaders: false,
                defaultCompressionMethod: CompressionMethod::STORE,
                defaultEnableZeroHeader: true,
                flushOutput: true,
            );

            try {
                foreach ($pendaftar as $peserta) {
                    $folder = $this->folderName($peserta);
                    $added = false;

                    foreach ($peserta->berkas as $berkas) {
                        if (! $disk->exists($berkas->path)) {
                            continue;
                        }

                        $absolute = $disk->path($berkas->path);
                        if (! is_readable($absolute)) {
                            throw new RuntimeException('Tidak dapat membaca berkas untuk ZIP.');
                        }

                        $zip->addFileFromPath(
                            fileName: $folder.'/'.$this->entryName($berkas),
                            path: $absolute,
                            compressionMethod: CompressionMethod::STORE,
                        );
                        $added = true;
                    }

                    if (! $added) {
                        $zip->addDirectory($folder.'/');
                    }
                }

                $this->writeKeputusanXlsx($pendaftar, $xlsxPath);
                $zip->addFileFromPath(
                    fileName: self::XLSX_NAME,
                    path: $xlsxPath,
                    compressionMethod: CompressionMethod::DEFLATE,
                );
                $zip->finish();
            } finally {
                if (is_file($xlsxPath)) {
                    @unlink($xlsxPath);
                }
            }
        }, $downloadName, [
            'Content-Type' => 'application/zip',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function selectionCacheKey(string $token): string
    {
        return 'toc_berkas_export_sel_'.$token;
    }

    public function folderName(PendaftarTeacherOfChampions $peserta): string
    {
        $name = $this->cleanSegment((string) $peserta->nama);
        $folder = $this->cleanSegment($peserta->nomor_pendaftaran.($name !== '' ? ' - '.$name : ''));

        $folder = rtrim(mb_substr($folder, 0, 80), " .\t");

        return $folder !== '' ? $folder : 'peserta-'.$peserta->getKey();
    }

    public function entryName(TocBerkas $berkas): string
    {
        $extension = strtolower((string) (pathinfo((string) $berkas->nama_asli, PATHINFO_EXTENSION)
            ?: pathinfo((string) $berkas->path, PATHINFO_EXTENSION)));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';

        return (self::ENTRY_BASENAME[$berkas->jenis] ?? 'berkas').'.'.$extension;
    }

    /**
     * Menulis ZIP (folder per peserta + keputusan-berkas.xlsx) ke path absolut.
     * Berkas ditambahkan lewat path disk sehingga tidak dimuat ke memori.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function buildZip(Collection $pendaftar, string $zipPath): int
    {
        $this->assertWithinLimit($pendaftar->count());

        $pendaftar->each(function (PendaftarTeacherOfChampions $peserta): void {
            $peserta->loadMissing('berkas');
        });

        $disk = Storage::disk(config('toc.berkas.disk'));
        $xlsxPath = $zipPath.'.xlsx.tmp';

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat berkas ZIP.');
        }

        try {
            foreach ($pendaftar as $peserta) {
                $folder = $this->folderName($peserta);
                $zip->addEmptyDir($folder);

                foreach ($peserta->berkas as $berkas) {
                    if (! $disk->exists($berkas->path)) {
                        continue;
                    }

                    // Stream from disk path — do not read file contents into PHP memory.
                    $absolute = $disk->path($berkas->path);
                    if (! is_readable($absolute) || ! $zip->addFile($absolute, $folder.'/'.$this->entryName($berkas))) {
                        throw new RuntimeException('Tidak dapat menambahkan berkas ke ZIP.');
                    }
                }
            }

            $this->writeKeputusanXlsx($pendaftar, $xlsxPath);
            if (! $zip->addFile($xlsxPath, self::XLSX_NAME)) {
                throw new RuntimeException('Tidak dapat menambahkan Excel keputusan ke ZIP.');
            }

            if (! $zip->close()) {
                throw new RuntimeException('Tidak dapat menutup berkas ZIP.');
            }
        } catch (Throwable $exception) {
            @$zip->close();
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }

            throw $exception;
        } finally {
            if (is_file($xlsxPath)) {
                @unlink($xlsxPath);
            }
        }

        return $pendaftar->count();
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function writeKeputusanXlsx(Collection $pendaftar, string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $keputusan = $spreadsheet->getActiveSheet();
        $keputusan->setTitle(self::SHEET_KEPUTUSAN);

        $headers = self::headers();
        foreach ($headers as $index => $header) {
            $keputusan->setCellValue([$index + 1, 1], $header);
        }
        $keputusan->getStyle('1:1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($pendaftar as $peserta) {
            foreach ($this->rowFor($peserta) as $columnIndex => $value) {
                $keputusan->setCellValue([$columnIndex + 1, $rowNumber], $value);
            }
            $rowNumber++;
        }

        $lastDataRow = max(2, $rowNumber - 1);
        $this->applyKeputusanValidation($keputusan, $lastDataRow);
        $this->protectMetaColumns($keputusan, $lastDataRow, count($headers));

        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle(self::SHEET_PETUNJUK);
        foreach ($this->petunjuk() as $index => $line) {
            $petunjuk->setCellValue([1, $index + 1], $line);
        }
        $petunjuk->getColumnDimension('A')->setWidth(120);

        $spreadsheet->setActiveSheetIndex(0);

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    private function applyKeputusanValidation(Worksheet $sheet, int $lastDataRow): void
    {
        foreach ([5, 8, 11] as $column) {
            $validation = $sheet->getCell([$column, 2])->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Keputusan tidak valid');
            $validation->setError('Isi hanya Terima, Tolak, atau biarkan kosong.');
            $validation->setFormula1('"Terima,Tolak"');
            $validation->setSqref(
                $sheet->getCell([$column, 2])->getCoordinate().':'
                .$sheet->getCell([$column, $lastDataRow])->getCoordinate()
            );
        }
    }

    private function protectMetaColumns(Worksheet $sheet, int $lastDataRow, int $headerCount): void
    {
        $sheet->getStyle([1, 1, $headerCount, $lastDataRow])
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        foreach ([5, 6, 8, 9, 11, 12] as $column) {
            $sheet->getStyle([$column, 2, $column, $lastDataRow])
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('toc-berkas-meta');
    }

    /**
     * @return list<string|int|null>
     */
    private function rowFor(PendaftarTeacherOfChampions $peserta): array
    {
        $byJenis = $peserta->berkas->keyBy('jenis');
        $row = [$peserta->nomor_pendaftaran, $peserta->nama, $peserta->bidang_studi];

        foreach (array_keys(self::COLUMN_PREFIX) as $jenis) {
            /** @var TocBerkas|null $berkas */
            $berkas = $byJenis->get($jenis);

            array_push($row, $berkas?->status ?? 'belum', '', '');
        }

        foreach (array_keys(self::COLUMN_PREFIX) as $jenis) {
            /** @var TocBerkas|null $berkas */
            $berkas = $byJenis->get($jenis);

            array_push($row, $berkas?->id, $berkas?->updated_at?->toIso8601String() ?? '');
        }

        return $row;
    }

    /**
     * @return list<string>
     */
    private function petunjuk(): array
    {
        return [
            'PETUNJUK PENGISIAN KEPUTUSAN BERKAS',
            '1. Isi kolom *_keputusan pada sheet "Keputusan" hanya dengan: Terima, Tolak, atau kosongkan.',
            '2. Kosong berarti tidak ada perubahan untuk berkas tersebut.',
            '3. Bila memilih Tolak, kolom *_alasan WAJIB diisi (alasan ini dikirim ke peserta lewat email).',
            '4. Jangan mengubah kolom nomor_pendaftaran, nama, bidang_studi, *_status_saat_ini, maupun kolom meta berawalan garis bawah (_foto_id, _foto_ver, _pakta_id, _pakta_ver, _surat_id, _surat_ver).',
            '5. Jangan mengubah nama kolom pada baris pertama dan jangan menghapus kolom.',
            '6. Bila peserta mengunggah ulang atau berkas sudah diverifikasi pihak lain setelah ekspor, barisnya dilewati saat impor (versi berkas berubah).',
            '7. Simpan sebagai .xlsx (maksimal 5 MB), lalu unggah lewat "Impor keputusan (Excel)". Pratinjau ditampilkan sebelum perubahan diterapkan.',
        ];
    }

    private function cleanSegment(string $value): string
    {
        $value = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F\x7F]+/u', '', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value, " .\t");
    }
}
