<?php

use App\Filament\Pages\TocDashboard;
use App\Filament\Widgets\Toc\TocBidangStudiWidget;
use App\Filament\Widgets\Toc\TocJenjangWidget;
use App\Filament\Widgets\Toc\TocStatsOverview;
use App\Filament\Widgets\Toc\TocTopProvincesWidget;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\TocDashboardStats;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
    config(['homepage.teacher_of_champions.registration_closes_at' => '2026-10-20 23:59']);
    $this->travelTo(CarbonImmutable::parse('2026-10-16 10:00', 'Asia/Jakarta')->utc());
});

function dashboardUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('counts registrants by status, WIB day and alumni share', function () {
    PendaftarTeacherOfChampions::factory()->create(['status' => 'baru', 'created_at' => CarbonImmutable::parse('2026-10-16 00:30', 'Asia/Jakarta')->utc()]);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'terverifikasi', 'created_at' => CarbonImmutable::parse('2026-10-16 09:00', 'Asia/Jakarta')->utc()]);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos', 'created_at' => CarbonImmutable::parse('2026-10-15 23:59', 'Asia/Jakarta')->utc(), 'asal_universitas' => 'USU']);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'ditolak', 'created_at' => CarbonImmutable::parse('2026-10-14 08:00', 'Asia/Jakarta')->utc(), 'asal_universitas' => 'UINSU', 'provinsi' => 'Aceh', 'jenjang' => 'SD', 'bidang_studi' => 'IPA']);

    $summary = app(TocDashboardStats::class)->summary();

    expect($summary['total'])->toBe(4)
        ->and($summary['today'])->toBe(2)
        ->and($summary['statuses'])->toBe(['baru' => 1, 'terverifikasi' => 1, 'lolos' => 1, 'ditolak' => 1])
        ->and($summary['alumni'])->toBe(2)
        ->and($summary['alumni_percentage'])->toBe(50.0)
        ->and($summary['daily']['2026-10-16'])->toBe(2)
        ->and($summary['daily']['2026-10-15'])->toBe(1)
        ->and($summary['daily']['2026-10-14'])->toBe(1)
        ->and(array_key_last($summary['daily']))->toBe('2026-10-16')
        ->and($summary['provinces'][0])->toBe(['label' => 'Sumatera Utara', 'total' => 3])
        ->and($summary['jenjang'])->toContain(['label' => 'SD/MI', 'total' => 1])
        ->and($summary['bidang_studi'][0])->toBe(['label' => 'Matematika', 'total' => 3]);
});

it('caches the summary for 60 seconds', function () {
    PendaftarTeacherOfChampions::factory()->create();
    expect(app(TocDashboardStats::class)->summary()['total'])->toBe(1);

    PendaftarTeacherOfChampions::factory()->create();
    expect(app(TocDashboardStats::class)->summary()['total'])->toBe(1);

    $this->travel(61)->seconds();
    expect(app(TocDashboardStats::class)->summary()['total'])->toBe(2);
});

it('opens the dashboard for ToC roles and admins', function (string $role) {
    $this->actingAs(dashboardUser($role))
        ->get(TocDashboard::getUrl())
        ->assertOk()
        ->assertSee('Dashboard Teacher of Champions');
})->with([User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC, 'admin', 'super_admin']);

it('forbids the dashboard without toc.dashboard.view', function () {
    $this->actingAs(dashboardUser('editor'))
        ->get(TocDashboard::getUrl())
        ->assertForbidden();
});

it('renders stats, countdown and breakdown widgets', function () {
    PendaftarTeacherOfChampions::factory()->count(3)->create();
    PendaftarTeacherOfChampions::factory()->create(['provinsi' => 'Aceh', 'jenjang' => 'SMP', 'bidang_studi' => 'Bahasa Indonesia', 'asal_universitas' => 'USU']);
    $this->actingAs(dashboardUser(User::ROLE_PANITIA_TOC));

    Livewire::test(TocStatsOverview::class)
        ->assertSee('Total pendaftar')
        ->assertSee('Sisa waktu pendaftaran')
        ->assertSee('4 hari 13 jam lagi')
        ->assertSee('75%');

    Livewire::test(TocTopProvincesWidget::class)->assertSee('Sumatera Utara')->assertSee('Aceh');
    Livewire::test(TocJenjangWidget::class)->assertSee('SMP/MTs');
    Livewire::test(TocBidangStudiWidget::class)->assertSee('Bahasa Indonesia');
});

it('shows the registration as closed after the deadline', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-21 08:00', 'Asia/Jakarta')->utc());
    $this->actingAs(dashboardUser(User::ROLE_ADMIN_TOC));

    Livewire::test(TocStatsOverview::class)->assertSee('Ditutup');
});
