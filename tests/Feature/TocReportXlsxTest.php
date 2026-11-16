<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use Carbon\CarbonImmutable;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

it('exports a single flat Statistik ToC sheet with Indonesian headers and no blank rows', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());

    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'DKI Jakarta',
        'jenjang' => 'SMA',
        'bidang_studi' => 'BAHASA INDONESIA',
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'dki  jakarta',
        'jenjang' => 'SMP',
        'bidang_studi' => 'Bahasa Indonesia',
        'asal_universitas' => 'USU',
        'created_at' => CarbonImmutable::parse('2026-10-16 09:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SD',
        'bidang_studi' => 'IPA',
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-15 08:00', 'Asia/Jakarta')->utc(),
    ]);

    TocDashboardStats::forget();
    $snapshot = app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'));
    $response = app(TocShareableReport::class)->downloadXlsx($snapshot);
    $path = $response->getFile()->getPathname();

    $reader = new XlsxReader;
    $reader->open($path);

    $sheetNames = [];
    $rows = [];
    $persenIsNumeric = true;

    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames[] = $sheet->getName();
        foreach ($sheet->getRowIterator() as $row) {
            $cells = $row->toArray();
            expect($cells)->not->toBeEmpty();
            expect(collect($cells)->every(fn ($v) => $v === null || $v === ''))->toBeFalse();
            $rows[] = $cells;

            if (count($rows) > 1 && ($cells[3] ?? '') !== '' && ($cells[3] ?? null) !== null) {
                $persenCell = $row->getCells()[3] ?? null;
                if ($persenCell !== null && ! $persenCell instanceof NumericCell && ! is_float($cells[3]) && ! is_int($cells[3])) {
                    $persenIsNumeric = false;
                }
            }
        }
    }
    $reader->close();

    expect($sheetNames)->toBe(['Statistik ToC'])
        ->and($rows[0])->toBe(['Bagian', 'Item', 'Jumlah', 'Persen (%)'])
        ->and($persenIsNumeric)->toBeTrue()
        ->and($response->headers->get('content-disposition'))->toContain('toc-statistik-');

    $bagianOrder = collect($rows)->skip(1)->pluck(0)->unique()->values()->all();
    expect($bagianOrder)->toBe(['Ringkasan', 'Status', 'Tren Harian', 'Provinsi', 'Jenjang', 'Bidang Studi']);

    $flat = collect($rows)->skip(1);
    expect($flat->firstWhere(fn ($r) => $r[1] === 'Total pendaftar')[2])->toBe($snapshot['total'])
        ->and($flat->firstWhere(fn ($r) => $r[1] === 'Alumni UNIMED')[3])->toBe($snapshot['alumni_percentage'])
        ->and($flat->firstWhere(fn ($r) => $r[1] === 'Dicetak pada')[2])->toContain('WIB')
        ->and($flat->firstWhere(fn ($r) => $r[1] === 'Pendaftaran ditutup')[2])->toStartWith('2026-10-29')
        ->and($flat->contains(fn ($r) => str_contains((string) $r[1], '@')))->toBeFalse()
        ->and($flat->contains(fn ($r) => str_contains(mb_strtolower((string) $r[1]), 'rahasia')))->toBeFalse();

    $provinsi = $flat->where(0, 'Provinsi')->values();
    expect($provinsi)->toHaveCount(2)
        ->and($provinsi[0][1])->toBe('DKI Jakarta');

    $bidang = $flat->where(0, 'Bidang Studi')->values();
    expect($bidang->firstWhere(fn ($r) => $r[1] === 'Bahasa Indonesia')[2])->toBe(2)
        ->and($bidang)->toHaveCount(2);

    $jenjang = $flat->where(0, 'Jenjang')->pluck(1)->all();
    expect($jenjang)->toContain('SMA/MA')
        ->and($jenjang)->toContain('SMP/MTs')
        ->and($jenjang)->toContain('SD/MI');

    $trenDates = $flat->where(0, 'Tren Harian')->pluck(1)->all();
    expect($trenDates[0])->toBe('2026-10-15')
        ->and($trenDates)->not->toContain('2026-10-14');
});

it('keeps province and bidang top-10 caps and case-insensitive grouping in flat rows', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());

    foreach (range(1, 12) as $i) {
        PendaftarTeacherOfChampions::factory()->create([
            'provinsi' => 'Provinsi '.$i,
            'bidang_studi' => 'Mapel '.$i,
            'jenjang' => 'SMA',
            'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
        ]);
    }
    PendaftarTeacherOfChampions::factory()->create([
        'provinsi' => 'PROVINSI 1',
        'bidang_studi' => 'mapel  1',
        'jenjang' => 'SMA',
        'created_at' => CarbonImmutable::parse('2026-10-17 11:00', 'Asia/Jakarta')->utc(),
    ]);

    TocDashboardStats::forget();
    $snapshot = app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'));
    $rows = app(TocShareableReport::class)->statistikFlatRows($snapshot);

    $provinsi = collect($rows)->where('bagian', 'Provinsi')->values();
    $bidang = collect($rows)->where('bagian', 'Bidang Studi')->values();

    expect($provinsi)->toHaveCount(10)
        ->and($bidang)->toHaveCount(10)
        ->and($provinsi->first()['item'])->toBe('Provinsi 1')
        ->and($provinsi->first()['jumlah'])->toBe(2)
        ->and($bidang->first()['item'])->toBe('Mapel 1')
        ->and($bidang->first()['jumlah'])->toBe(2)
        ->and(collect($rows)->contains(fn ($r) => $r['jumlah'] === null && $r['item'] === ''))->toBeFalse();
});
