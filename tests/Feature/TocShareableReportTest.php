<?php

use App\Filament\Pages\TocDashboard;
use App\Models\AuditLog;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
    config(['homepage.teacher_of_champions.registration_closes_at' => '2026-10-29 23:59']);
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());
    TocDashboardStats::forget();
});

function tocShareUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function seedShareableRegistrants(): void
{
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SD',
        'bidang_studi' => 'Matematika',
        'asal_universitas' => 'Universitas Negeri Medan',
        'created_at' => CarbonImmutable::parse('2026-10-17 09:00', 'Asia/Jakarta')->utc(),
        'nama' => 'Rahasia Nama',
        'email' => 'rahasia@example.com',
        'whatsapp' => '6281111111111',
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Aceh',
        'jenjang' => 'SMA',
        'bidang_studi' => 'BAHASA INDONESIA',
        'asal_universitas' => 'USU',
        'created_at' => CarbonImmutable::parse('2026-10-16 10:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SMP',
        'bidang_studi' => 'Bahasa Indonesia',
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-17 11:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Jawa Barat',
        'jenjang' => 'SMK',
        'bidang_studi' => 'IPA',
        'asal_universitas' => 'UMSU',
        'created_at' => CarbonImmutable::parse('2026-10-15 08:00', 'Asia/Jakarta')->utc(),
    ]);
}

it('builds the WhatsApp summary in the locked share format without personal fields', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $now = CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta');
    $snapshot = app(TocDashboardStats::class)->snapshot($now);
    $text = app(TocShareableReport::class)->whatsappSummary($snapshot);

    expect($text)->toBe(implode("\n", [
        '*Update Pendaftar Teacher of Champions*',
        'Per Sabtu, 17 Okt 2026, 16.23 WIB',
        '',
        'Total pendaftar: 4',
        'Hari ini: 2',
        'Alumni UNIMED: 2 (50%)',
        '',
        '*Status*',
        'Baru: 4 · Terverifikasi: 0 · Lolos: 0 · Ditolak: 0',
        '',
        '*Top Provinsi*',
        '1. Sumatera Utara (2)',
        '2. Aceh (1)',
        '3. Jawa Barat (1)',
        '',
        '*Jenjang*',
        'SD/MI 1 · SMA/MA 1 · SMK/MAK 1 · SMP/MTs 1',
        '',
        '*Bidang Studi Teratas*',
        '1. Bahasa Indonesia (2)',
        '2. IPA (1)',
        '3. Matematika (1)',
        '',
        'Sisa waktu: 12 hari 7 jam menuju 29 Okt 2026 23.59 WIB',
        'Daftar: ikaunimed.or.id/toc',
    ]))
        ->and($text)->not->toContain('Rahasia Nama')
        ->and($text)->not->toContain('rahasia@example.com')
        ->and($text)->not->toContain('6281111111111');
});

it('uses one snapshot so WhatsApp, image and spreadsheet numbers stay identical', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $snapshot = app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'));
    $report = app(TocShareableReport::class);
    $text = $report->whatsappSummary($snapshot);
    $image = $report->imagePayload($snapshot);

    expect($image['total'])->toBe($snapshot['total'])
        ->and($image['today'])->toBe($snapshot['today'])
        ->and($image['alumni'])->toBe($snapshot['alumni'])
        ->and($text)->toContain('Total pendaftar: '.TocDashboardStats::formatNumber($snapshot['total']))
        ->and($text)->toContain('Hari ini: '.TocDashboardStats::formatNumber($snapshot['today']));
});

it('groups bidang studi case-insensitively for display only', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $snapshot = app(TocDashboardStats::class)->snapshot();

    expect($snapshot['bidang_studi'][0])->toBe(['label' => 'Bahasa Indonesia', 'total' => 2]);
});

it('renders a real 1080x1350 PNG without personal data', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $binary = app(TocShareableReport::class)->renderPngBinary(
        app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'))
    );

    $image = imagecreatefromstring($binary);

    expect($binary)->toStartWith("\x89PNG")
        ->and(imagesx($image))->toBe(1080)
        ->and(imagesy($image))->toBe(1350)
        ->and($binary)->not->toContain('rahasia@example.com')
        ->and($binary)->not->toContain('6281111111111');

    imagedestroy($image);

    $dir = storage_path('app/testing');
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dir.'/toc-update-preview.png', $binary);
});

it('exports a multi-sheet XLSX with Indonesian headers', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $response = app(TocShareableReport::class)->downloadXlsx(
        app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'))
    );

    $path = $response->getFile()->getPathname();
    $reader = new XlsxReader;
    $reader->open($path);
    $sheets = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $sheets[] = $sheet->getName();
    }
    $reader->close();

    expect($sheets)->toBe(['Ringkasan', 'Tren Harian', 'Provinsi', 'Jenjang', 'Bidang Studi'])
        ->and($response->headers->get('content-disposition'))->toContain('toc-statistik-');
});

it('allows panitia-toc to open share actions and hides XLSX without export permission', function () {
    $this->actingAs(tocShareUser(User::ROLE_PANITIA_TOC))
        ->get(TocDashboard::getUrl())
        ->assertOk()
        ->assertSee('Salin ringkasan WhatsApp')
        ->assertSee('Unduh gambar laporan')
        ->assertSee('Cetak / simpan PDF');

    Livewire\Livewire::test(TocDashboard::class)
        ->assertActionHidden('eksporStatistikXlsx');
});

it('forbids users without toc.dashboard.view from opening the dashboard', function () {
    $this->actingAs(tocShareUser('editor'))
        ->get(TocDashboard::getUrl())
        ->assertForbidden();
});

it('records an audit entry without report body content when a share is logged', function () {
    $this->actingAs(tocShareUser(User::ROLE_PANITIA_TOC));

    Livewire\Livewire::test(TocDashboard::class)
        ->call('recordLaporanShare', 'whatsapp');

    $log = AuditLog::query()->where('aksi', 'laporan.dibagikan')->sole();

    expect($log->perubahan)->toBe(['jenis' => 'whatsapp'])
        ->and($log->perubahan)->not->toHaveKey('text');
});

it('allows admin-toc to export the XLSX action', function () {
    seedShareableRegistrants();
    TocDashboardStats::forget();

    $this->actingAs(tocShareUser(User::ROLE_ADMIN_TOC));

    Livewire\Livewire::test(TocDashboard::class)
        ->assertActionVisible('eksporStatistikXlsx')
        ->callAction('eksporStatistikXlsx')
        ->assertSuccessful();
});
