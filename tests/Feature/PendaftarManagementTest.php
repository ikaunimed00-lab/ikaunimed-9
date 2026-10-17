<?php

use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\PendaftarDuplicateDetector;
use App\Services\PendaftarTeacherOfChampionsExporter;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
});

function actingAsToc(string $role = User::ROLE_ADMIN_TOC): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

/**
 * @return list<list<string>>
 */
function downloadedCsvRows($component): array
{
    $content = base64_decode($component->effects['download']['content']);

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    return collect(preg_split('/\r?\n/', trim(substr($content, 3))))
        ->map(fn (string $line): array => str_getcsv($line))
        ->all();
}

it('filters registrants by status, jenjang, provinsi and WIB registration date', function () {
    actingAsToc();
    $lolos = PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos', 'jenjang' => 'SD', 'provinsi' => 'Aceh', 'created_at' => CarbonImmutable::parse('2026-10-10 00:30', 'Asia/Jakarta')->utc()]);
    $baru = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru', 'jenjang' => 'SMA', 'created_at' => CarbonImmutable::parse('2026-10-09 23:30', 'Asia/Jakarta')->utc()]);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->filterTable('status', 'lolos')
        ->assertCanSeeTableRecords([$lolos])
        ->assertCanNotSeeTableRecords([$baru])
        ->resetTableFilters()
        ->filterTable('jenjang', 'SMA')
        ->assertCanSeeTableRecords([$baru])
        ->assertCanNotSeeTableRecords([$lolos])
        ->resetTableFilters()
        ->filterTable('provinsi', 'Aceh')
        ->assertCanSeeTableRecords([$lolos])
        ->assertCanNotSeeTableRecords([$baru])
        ->resetTableFilters()
        ->filterTable('tanggal_daftar', ['dari' => '2026-10-10', 'sampai' => '2026-10-10'])
        ->assertCanSeeTableRecords([$lolos])
        ->assertCanNotSeeTableRecords([$baru])
        ->filterTable('tanggal_daftar', ['dari' => null, 'sampai' => '2026-10-09'])
        ->assertCanSeeTableRecords([$baru])
        ->assertCanNotSeeTableRecords([$lolos]);
});

it('searches by name, number, WhatsApp, email and school', function (string $field, string $term) {
    actingAsToc();
    $target = PendaftarTeacherOfChampions::factory()->create([
        'nama' => 'Rahmawati Siregar',
        'nomor_pendaftaran' => 'TOC-2026-0777',
        'whatsapp' => '6281399990000',
        'email' => 'rahma.unik@example.com',
        'asal_instansi' => 'SD Swasta Cahaya Ilmu',
    ]);
    $other = PendaftarTeacherOfChampions::factory()->create();

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->searchTable($term)
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
})->with([
    'nama' => ['nama', 'Rahmawati'],
    'nomor' => ['nomor', 'TOC-2026-0777'],
    'whatsapp' => ['whatsapp', '6281399990000'],
    'email' => ['email', 'rahma.unik@'],
    'sekolah' => ['sekolah', 'Cahaya Ilmu'],
]);

it('does not let panitia-toc search by hidden contact data', function () {
    actingAsToc(User::ROLE_PANITIA_TOC);
    $target = PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '6281399990000']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->searchTable('6281399990000')
        ->assertCanNotSeeTableRecords([$target]);
});

it('applies bulk status changes only for sensible transitions', function () {
    actingAsToc();
    $baru = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);
    $terverifikasi = PendaftarTeacherOfChampions::factory()->create(['status' => 'terverifikasi']);
    $lolos = PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos']);
    $ditolak = PendaftarTeacherOfChampions::factory()->create(['status' => 'ditolak']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableBulkAction('tandaiTerverifikasi', [$baru, $lolos, $ditolak])
        ->assertNotified('1 pendaftar diubah menjadi Terverifikasi');

    expect($baru->refresh()->status)->toBe('terverifikasi')
        ->and($lolos->refresh()->status)->toBe('lolos')
        ->and($ditolak->refresh()->status)->toBe('ditolak');

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableBulkAction('tandaiLolos', [$baru, $terverifikasi, $ditolak]);

    expect($baru->refresh()->status)->toBe('lolos')
        ->and($terverifikasi->refresh()->status)->toBe('lolos')
        ->and($ditolak->refresh()->status)->toBe('ditolak');

    $another = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableBulkAction('tandaiDitolak', [$another, $lolos]);

    expect($another->refresh()->status)->toBe('ditolak')
        ->and($lolos->refresh()->status)->toBe('lolos');
});

it('reports skipped registrants in bulk actions', function () {
    $records = collect([
        PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']),
        PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos']),
        PendaftarTeacherOfChampions::factory()->create(['status' => 'terverifikasi']),
    ]);

    expect(App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource::bulkUpdateStatus($records, 'terverifikasi'))
        ->toBe(['updated' => 1, 'skipped' => 2]);
});

it('blocks bulk status changes for panitia-toc', function () {
    actingAsToc(User::ROLE_PANITIA_TOC);
    $baru = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertTableBulkActionHidden('tandaiTerverifikasi')
        ->assertTableBulkActionHidden('tandaiLolos')
        ->assertTableBulkActionHidden('tandaiDitolak')
        ->assertTableActionHidden('edit', $baru);

    expect(auth()->user()->can('update', $baru))->toBeFalse();
});

it('saves status and internal notes from the edit action', function () {
    actingAsToc();
    $pendaftar = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableAction('edit', $pendaftar, data: [
            'status' => 'terverifikasi',
            'catatan' => 'Berkas lengkap',
            'catatan_internal' => 'Sudah ditelepon panitia',
        ])
        ->assertHasNoTableActionErrors();

    expect($pendaftar->refresh())
        ->status->toBe('terverifikasi')
        ->catatan->toBe('Berkas lengkap')
        ->catatan_internal->toBe('Sudah ditelepon panitia');
});

it('flags possible duplicates by WhatsApp, email and similar name with school', function () {
    $waA = PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '081234567890']);
    $waB = PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '+62 812-3456-7890']);
    $emailA = PendaftarTeacherOfChampions::factory()->create(['email' => 'Guru.Hebat@Example.com']);
    $emailB = PendaftarTeacherOfChampions::factory()->create(['email' => 'guru.hebat@example.com ']);
    $nameA = PendaftarTeacherOfChampions::factory()->create(['nama' => 'Budi Santoso, S.Pd.', 'asal_instansi' => 'SMA Negeri 1 Medan']);
    $nameB = PendaftarTeacherOfChampions::factory()->create(['nama' => 'Budi Santosa', 'asal_instansi' => 'SMAN 1 Medan']);
    $sameNameOtherSchool = PendaftarTeacherOfChampions::factory()->create(['nama' => 'Budi Santoso', 'asal_instansi' => 'SD Negeri 5 Binjai']);
    $unique = PendaftarTeacherOfChampions::factory()->create(['nama' => 'Siti Aminah', 'asal_instansi' => 'SMP Negeri 2 Deli Serdang']);

    $duplicates = app(PendaftarDuplicateDetector::class)->duplicates();

    expect(array_keys($duplicates))->toEqualCanonicalizing([$waA->id, $waB->id, $emailA->id, $emailB->id, $nameA->id, $nameB->id])
        ->and($duplicates[$waA->id][0])->toBe("WhatsApp sama dengan {$waB->nomor_pendaftaran}")
        ->and($duplicates[$nameB->id][0])->toBe("Nama & sekolah mirip dengan {$nameA->nomor_pendaftaran}")
        ->and($duplicates)->not->toHaveKeys([$sameNameOtherSchool->id, $unique->id]);

    actingAsToc(User::ROLE_PANITIA_TOC);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertSee('Kemungkinan duplikat')
        ->filterTable('kemungkinan_duplikat', true)
        ->assertCanSeeTableRecords([$waA, $waB, $emailA, $emailB, $nameA, $nameB])
        ->assertCanNotSeeTableRecords([$sameNameOtherSchool, $unique]);
});

it('exports only the filtered registrants as CSV with BOM and text-safe numbers', function () {
    actingAsToc();
    $lolos = PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos', 'whatsapp' => '6281234567890', 'nama' => '=HYPERLINK("x")']);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    $component = Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->filterTable('status', 'lolos')
        ->callAction('ekspor', data: ['format' => 'csv'])
        ->assertFileDownloaded();

    $rows = downloadedCsvRows($component);

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe($lolos->nomor_pendaftaran)
        ->and($rows[1][1])->toBe('\'=HYPERLINK("x")')
        ->and($rows[1][2])->toBe('="6281234567890"')
        ->and($rows[0])->toContain('Catatan Internal');
});

it('writes WhatsApp and registration numbers as text cells in Excel', function () {
    PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '6281234567890', 'nama' => '=1+1']);

    $response = app(PendaftarTeacherOfChampionsExporter::class)
        ->download(PendaftarTeacherOfChampions::query()->get(), 'xlsx');

    $reader = new Reader;
    $reader->open($response->getFile()->getPathname());
    $cells = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($index === 2) {
                $cells = $row->toArray();
            }
        }
    }

    $reader->close();

    expect($cells[2])->toBe('6281234567890')
        ->and($cells[1])->toBe('=1+1');
});

it('exports a single Pendaftar sheet with freeze, autofilter, and normalized display labels', function () {
    PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6289999888777',
        'jenjang' => 'SMA',
        'provinsi' => 'dki  jakarta',
        'kabupaten_kota' => 'KOTA  MEDAN',
        'bidang_studi' => 'BAHASA INDONESIA',
        'asal_universitas' => 'universitas negeri medan (unimed)',
        'status_kepegawaian' => 'non-ASN',
    ]);

    $response = app(PendaftarTeacherOfChampionsExporter::class)
        ->download(PendaftarTeacherOfChampions::query()->get(), 'xlsx');

    $path = $response->getFile()->getPathname();
    $reader = new Reader;
    $reader->open($path);

    $sheetNames = [];
    $header = [];
    $row = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames[] = $sheet->getName();
        foreach ($sheet->getRowIterator() as $index => $excelRow) {
            if ($index === 1) {
                $header = $excelRow->toArray();
            }
            if ($index === 2) {
                $row = $excelRow->toArray();
            }
        }
    }
    $reader->close();

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
    $workbookXml = (string) $zip->getFromName('xl/workbook.xml');
    $zip->close();

    expect($sheetNames)->toBe(['Pendaftar'])
        ->and($header)->toBe(array_values(PendaftarTeacherOfChampionsExporter::COLUMNS))
        ->and($row[array_search('Jenjang', $header, true)])->toBe('SMA/MA')
        ->and($row[array_search('Provinsi', $header, true)])->toBe('DKI Jakarta')
        ->and($row[array_search('Kabupaten/Kota', $header, true)])->toBe('Kota Medan')
        ->and($row[array_search('Bidang Studi', $header, true)])->toBe('Bahasa Indonesia')
        ->and($row[array_search('Asal Universitas (S1)', $header, true)])->toContain('UNIMED')
        ->and($row[array_search('WhatsApp', $header, true)])->toBe('6289999888777')
        ->and($sheetXml)->toContain('autoFilter')
        ->and($sheetXml)->toContain('state="frozen"')
        ->and($workbookXml)->toContain('Pendaftar');
});

it('hides export from panitia-toc', function () {
    actingAsToc(User::ROLE_PANITIA_TOC);
    PendaftarTeacherOfChampions::factory()->create();

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertActionHidden('ekspor')
        ->assertTableBulkActionHidden('eksporTerpilih');

    expect(auth()->user()->can('export', PendaftarTeacherOfChampions::class))->toBeFalse();
});
