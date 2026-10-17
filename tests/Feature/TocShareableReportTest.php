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

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
    config(['homepage.teacher_of_champions.registration_closes_at' => '2026-10-27 23:59']);
    $this->travelTo(CarbonImmutable::parse('2026-10-16 14:30', 'Asia/Jakarta')->utc());
    TocDashboardStats::forget();
});

function tocShareUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('builds a WhatsApp summary from aggregate data without personal fields', function () {
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SD',
        'bidang_studi' => 'Matematika',
        'asal_universitas' => 'Universitas Negeri Medan',
        'created_at' => CarbonImmutable::parse('2026-10-16 09:00', 'Asia/Jakarta')->utc(),
        'nama' => 'Rahasia Nama',
        'email' => 'rahasia@example.com',
        'whatsapp' => '6281111111111',
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'terverifikasi',
        'provinsi' => 'Aceh',
        'jenjang' => 'SMA',
        'bidang_studi' => 'IPA',
        'asal_universitas' => 'USU',
        'created_at' => CarbonImmutable::parse('2026-10-15 10:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'lolos',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SMP',
        'bidang_studi' => 'Matematika',
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-16 11:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'ditolak',
        'provinsi' => 'Sumatera Utara',
        'jenjang' => 'SMK',
        'bidang_studi' => 'Bahasa Indonesia',
        'asal_universitas' => 'UMSU',
        'created_at' => CarbonImmutable::parse('2026-10-14 08:00', 'Asia/Jakarta')->utc(),
    ]);

    TocDashboardStats::forget();
    $text = app(TocShareableReport::class)->whatsappSummary();

    expect($text)->toContain('*Update Pendaftar Teacher of Champions*')
        ->and($text)->toContain('Jumat, 16 Oktober 2026, 14.30 WIB')
        ->and($text)->toContain('Total: 4 | Hari ini: 2')
        ->and($text)->toContain('Status: baru 1 · terverifikasi 1 · lolos 1 · ditolak 1')
        ->and($text)->toContain('Alumni UNIMED: 2 (50%)')
        ->and($text)->toContain('Top provinsi: 1) Sumatera Utara 3 2) Aceh 1')
        ->and($text)->toContain('Jenjang:')
        ->and($text)->toContain('Bidang studi teratas: 1) Matematika 2')
        ->and($text)->toContain('Sisa waktu: 11 hari menuju 27 Okt 2026 23.59 WIB')
        ->and($text)->toContain('Info: ikaunimed.or.id/toc')
        ->and($text)->not->toContain('Rahasia Nama')
        ->and($text)->not->toContain('rahasia@example.com')
        ->and($text)->not->toContain('6281111111111');
});

it('allows panitia-toc to open the dashboard share actions page', function () {
    $this->actingAs(tocShareUser(User::ROLE_PANITIA_TOC))
        ->get(TocDashboard::getUrl())
        ->assertOk()
        ->assertSee('Salin ringkasan WhatsApp');
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

it('hides CSV export from panitia-toc and allows it for admin-toc', function () {
    PendaftarTeacherOfChampions::factory()->count(2)->create();
    TocDashboardStats::forget();

    $this->actingAs(tocShareUser(User::ROLE_PANITIA_TOC));
    Livewire\Livewire::test(TocDashboard::class)
        ->assertActionHidden('eksporStatistikCsv');

    $this->actingAs(tocShareUser(User::ROLE_ADMIN_TOC));
    Livewire\Livewire::test(TocDashboard::class)
        ->assertActionVisible('eksporStatistikCsv')
        ->callAction('eksporStatistikCsv')
        ->assertSuccessful();
});
