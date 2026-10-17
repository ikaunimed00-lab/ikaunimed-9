<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use Carbon\CarbonImmutable;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

it('locks the aggregate XLSX workbook sheet order and ringkasan headers', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());
    PendaftarTeacherOfChampions::factory()->create([
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SMA',
        'bidang_studi' => 'Matematika',
        'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
    ]);
    TocDashboardStats::forget();

    $response = app(TocShareableReport::class)->downloadXlsx(
        app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'))
    );

    $path = $response->getFile()->getPathname();
    $reader = new XlsxReader;
    $reader->open($path);

    $sheetNames = [];
    $ringkasanHeaders = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames[] = $sheet->getName();
        if ($sheet->getName() === 'Ringkasan') {
            foreach ($sheet->getRowIterator() as $index => $row) {
                $cells = $row->toArray();
                if (($cells[0] ?? null) === 'Metrik') {
                    $ringkasanHeaders = $cells;
                    break;
                }
            }
        }
    }
    $reader->close();

    expect($sheetNames)->toBe(['Ringkasan', 'Tren Harian', 'Provinsi', 'Jenjang', 'Bidang Studi'])
        ->and($ringkasanHeaders)->toBe(['Metrik', 'Nilai']);
});
