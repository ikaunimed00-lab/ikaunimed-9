<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
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
        'created_at' => 'Tanggal Daftar (WIB)',
    ];

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     */
    public function download(Collection $pendaftar, string $format = 'xlsx'): BinaryFileResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';
        $path = tempnam(sys_get_temp_dir(), 'toc').'.'.$format;
        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;

        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(array_values(self::COLUMNS)));

        foreach ($pendaftar as $item) {
            $writer->addRow(Row::fromValues($this->rowValues($item)));
        }

        $writer->close();

        $fileName = 'pendaftar-teacher-of-champions-'.now(Kegiatan::TIMEZONE)->format('Ymd-His').'.'.$format;

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    /**
     * @return list<string|int|null>
     */
    private function rowValues(PendaftarTeacherOfChampions $item): array
    {
        return collect(array_keys(self::COLUMNS))
            ->map(fn (string $column): string|int|null => match ($column) {
                'status' => PendaftarTeacherOfChampions::STATUSES[$item->status] ?? $item->status,
                'jenjang' => $item->jenjang === null ? null : (PendaftarTeacherOfChampions::JENJANG[$item->jenjang] ?? $item->jenjang),
                'alumni_unimed' => match ($item->alumni_unimed) {
                    true => 'Ya',
                    false => 'Tidak',
                    default => null,
                },
                'created_at' => $item->created_at?->copy()->setTimezone(Kegiatan::TIMEZONE)->format('Y-m-d H:i'),
                default => $item->{$column},
            })
            ->all();
    }
}
