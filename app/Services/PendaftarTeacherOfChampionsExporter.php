<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PendaftarTeacherOfChampionsExporter
{
    /**
     * @var array<string, string>
     */
    public const COLUMNS = [
        'nomor_pendaftaran' => 'Nomor Pendaftaran',
        'nama' => 'Nama',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'provinsi' => 'Provinsi',
        'kabupaten_kota' => 'Kabupaten/Kota',
        'asal_instansi' => 'Asal Instansi',
        'jenjang' => 'Jenjang',
        'status_kepegawaian' => 'Status Kepegawaian',
        'bidang_studi' => 'Bidang Studi',
        'lama_mengabdi_tahun' => 'Lama Mengabdi (tahun)',
        'asal_universitas' => 'Asal Universitas (S1)',
        'alumni_unimed' => 'Alumni UNIMED',
        'instagram' => 'Instagram',
        'status' => 'Status',
        'catatan' => 'Catatan',
        'catatan_internal' => 'Catatan Internal',
        'created_at' => 'Tanggal Daftar (WIB)',
    ];

    /**
     * Long digit strings that spreadsheet apps would otherwise turn into numbers (e.g. 6.28E+12).
     *
     * @var list<string>
     */
    public const TEXT_COLUMNS = ['nomor_pendaftaran', 'whatsapp'];

    /**
     * Approximate column widths (OpenSpout index starts at 1).
     *
     * @var array<int, float>
     */
    private const COLUMN_WIDTHS = [
        1 => 18,
        2 => 28,
        3 => 16,
        4 => 28,
        5 => 18,
        6 => 20,
        7 => 24,
        8 => 12,
        9 => 16,
        10 => 22,
        11 => 12,
        12 => 30,
        13 => 12,
        14 => 16,
        15 => 12,
        16 => 18,
        17 => 18,
        18 => 18,
    ];

    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     * @param  array<string, mixed>  $auditContext  e.g. the active table filters and search
     */
    public function download(Collection $pendaftar, string $format = 'xlsx', array $auditContext = []): BinaryFileResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';

        $this->auditLogger->record('pendaftar.exported', null, [
            'format' => $format,
            'jumlah' => $pendaftar->count(),
            ...$auditContext,
        ]);
        $path = tempnam(sys_get_temp_dir(), 'toc').'.'.$format;

        if ($format === 'csv') {
            $options = new CsvOptions;
            $options->SHOULD_ADD_BOM = true;
            $writer = new CsvWriter($options);
            $writer->openToFile($path);
            $writer->addRow(Row::fromValues(array_values(self::COLUMNS)));

            foreach ($pendaftar as $item) {
                $writer->addRow(new Row($this->cells($item, $format)));
            }

            $writer->close();
        } else {
            $this->writeXlsx($path, $pendaftar);
        }

        $fileName = 'pendaftar-teacher-of-champions-'.now(Kegiatan::TIMEZONE)->format('Ymd-His').'.'.$format;

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    private function writeXlsx(string $path, Collection $pendaftar): void
    {
        $headerStyle = (new Style)->setFontBold();
        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Pendaftar');
        $sheet->setSheetView((new SheetView)->setFreezeRow(2));

        foreach (self::COLUMN_WIDTHS as $index => $width) {
            $sheet->setColumnWidth($width, $index);
        }

        $writer->addRow(Row::fromValues(array_values(self::COLUMNS), $headerStyle));

        foreach ($pendaftar as $item) {
            $writer->addRow(new Row($this->cells($item, 'xlsx')));
        }

        $lastRow = max(1, $sheet->getWrittenRowCount());
        $sheet->setAutoFilter(new AutoFilter(0, 1, count(self::COLUMNS) - 1, $lastRow));

        $writer->close();
    }

    /**
     * @return list<Cell>
     */
    private function cells(PendaftarTeacherOfChampions $item, string $format): array
    {
        return collect($this->rowValues($item))
            ->map(function (string|int|null $value, string $column) use ($format): Cell {
                if (! is_string($value)) {
                    return Cell::fromValue($value);
                }

                if ($format === 'csv' && in_array($column, self::TEXT_COLUMNS, true) && ctype_digit($value)) {
                    return new StringCell('="'.$value.'"', null);
                }

                if ($format === 'xlsx' && in_array($column, self::TEXT_COLUMNS, true)) {
                    return new StringCell($value, null);
                }

                return new StringCell($this->neutralizeFormula($value, $format), null);
            })
            ->values()
            ->all();
    }

    /**
     * Registrant input must never be evaluated as a spreadsheet formula when the CSV is opened.
     */
    private function neutralizeFormula(string $value, string $format): string
    {
        if ($format === 'csv' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Display-only values for export (database rows are unchanged).
     *
     * @return array<string, string|int|null>
     */
    private function rowValues(PendaftarTeacherOfChampions $item): array
    {
        return collect(array_keys(self::COLUMNS))
            ->mapWithKeys(fn (string $column): array => [$column => match ($column) {
                'status' => PendaftarTeacherOfChampions::STATUSES[$item->status] ?? $item->status,
                'jenjang' => TocDashboardStats::jenjangLabel($item->jenjang),
                'provinsi' => $this->displayOrEmpty($item->provinsi),
                'kabupaten_kota' => $this->displayOrEmpty($item->kabupaten_kota),
                'bidang_studi' => $this->displayOrEmpty($item->bidang_studi),
                'asal_universitas' => $this->displayOrEmpty($item->asal_universitas),
                'status_kepegawaian' => $this->displayOrEmpty($item->status_kepegawaian),
                'alumni_unimed' => match ($item->alumni_unimed) {
                    true => 'Ya',
                    false => 'Tidak',
                    default => null,
                },
                'created_at' => $item->created_at?->copy()->setTimezone(Kegiatan::TIMEZONE)->format('Y-m-d H:i'),
                default => $item->{$column},
            }])
            ->all();
    }

    private function displayOrEmpty(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return TocDashboardStats::EMPTY_LABEL;
        }

        return TocDashboardStats::displayLabel($value);
    }
}
