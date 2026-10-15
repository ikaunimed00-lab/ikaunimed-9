<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
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
        } else {
            $writer = new XlsxWriter;
        }

        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_values(self::COLUMNS)));

        foreach ($pendaftar as $item) {
            $writer->addRow(new Row($this->cells($item, $format)));
        }

        $writer->close();

        $fileName = 'pendaftar-teacher-of-champions-'.now(Kegiatan::TIMEZONE)->format('Ymd-His').'.'.$format;

        return response()->download($path, $fileName)->deleteFileAfterSend();
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
     * @return array<string, string|int|null>
     */
    private function rowValues(PendaftarTeacherOfChampions $item): array
    {
        return collect(array_keys(self::COLUMNS))
            ->mapWithKeys(fn (string $column): array => [$column => match ($column) {
                'status' => PendaftarTeacherOfChampions::STATUSES[$item->status] ?? $item->status,
                'jenjang' => $item->jenjang === null ? null : (PendaftarTeacherOfChampions::JENJANG[$item->jenjang] ?? $item->jenjang),
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
}
