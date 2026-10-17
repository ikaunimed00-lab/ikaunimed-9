<?php

use App\Models\AuditLog;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    config(['homepage.teacher_of_champions.registration_closes_at' => '2026-10-29 23:59']);
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());
    TocDashboardStats::forget();
});

function tocPrintUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('renders the dedicated ToC print page for panitia without chrome or personal data', function () {
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'DKI Jakarta',
        'jenjang' => 'SMA',
        'bidang_studi' => 'BAHASA INDONESIA',
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
        'nama' => 'Rahasia Nama',
        'email' => 'rahasia@example.com',
        'whatsapp' => '6281111111111',
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'status' => 'baru',
        'provinsi' => 'dki jakarta',
        'jenjang' => 'SMP',
        'bidang_studi' => 'Bahasa Indonesia',
        'asal_universitas' => 'USU',
        'created_at' => CarbonImmutable::parse('2026-10-15 09:00', 'Asia/Jakarta')->utc(),
    ]);

    TocDashboardStats::forget();
    $snapshot = app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'));
    $whatsapp = app(TocShareableReport::class)->whatsappSummary($snapshot);

    $response = $this->actingAs(tocPrintUser(User::ROLE_PANITIA_TOC))
        ->get(route('toc.print-report'));

    $response->assertOk()
        ->assertSee('Teacher of Champions — Update Pendaftar', false)
        ->assertSee('IKA UNIMED', false)
        ->assertSee('ikaunimed.or.id/toc', false)
        ->assertSee('DKI Jakarta', false)
        ->assertSee('Bahasa Indonesia', false)
        ->assertSee('SMA/MA', false)
        ->assertSee('Pendaftar per hari', false)
        ->assertSee('Status kepegawaian', false)
        ->assertSee('Top 5 Kampus Asal', false)
        ->assertDontSee('Rahasia Nama', false)
        ->assertDontSee('rahasia@example.com', false)
        ->assertDontSee('6281111111111', false)
        ->assertDontSee('>Menu<', false)
        ->assertDontSee('fi-sidebar', false);

    expect($whatsapp)->toContain('Total pendaftar: '.TocDashboardStats::formatNumber($snapshot['total']))
        ->and($response->getContent())->toContain((string) $snapshot['total']);
});

it('records an audit entry when opened with autoprint and forbids users without permission', function () {
    $this->actingAs(tocPrintUser(User::ROLE_PANITIA_TOC))
        ->get(route('toc.print-report', ['autoprint' => 1]))
        ->assertOk()
        ->assertSee('window.print()', false);

    expect(AuditLog::query()->where('aksi', 'laporan.dibagikan')->where('perubahan->jenis', 'print')->count())->toBe(1);

    $this->actingAs(tocPrintUser('editor'))
        ->get(route('toc.print-report'))
        ->assertForbidden();
});

it('keeps print payload numbers identical to the shared snapshot', function () {
    PendaftarTeacherOfChampions::factory()->count(3)->create([
        'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
        'asal_universitas' => 'Universitas Negeri Medan',
    ]);

    TocDashboardStats::forget();
    $snapshot = app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'));
    $print = app(TocShareableReport::class)->printPayload($snapshot);
    $xlsxRows = app(TocShareableReport::class)->statistikFlatRows($snapshot);

    expect($print['total'])->toBe($snapshot['total'])
        ->and($print['today'])->toBe($snapshot['today'])
        ->and($print['alumni'])->toBe($snapshot['alumni'])
        ->and($print['closes_at_label'])->toContain('29 Okt 2026')
        ->and(collect($xlsxRows)->firstWhere('item', 'Total pendaftar')['jumlah'])->toBe($snapshot['total'])
        ->and(count($print['provinces']))->toBeLessThanOrEqual(5)
        ->and(count($print['bidang_studi']))->toBeLessThanOrEqual(5)
        ->and(count($print['daily_last7']))->toBeLessThanOrEqual(7)
        ->and($print['kepegawaian'])->not->toBeEmpty()
        ->and(count($print['kampus']))->toBeLessThanOrEqual(5);
});
