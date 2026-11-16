<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Throwable;
use ZipArchive;

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
