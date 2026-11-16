<?php

use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ViewPendaftarTeacherOfChampions;
use App\Models\AuditLog;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
});

function actingAsTocRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('lets admin-toc view full contacts and records an audit log', function () {
    actingAsTocRole(User::ROLE_ADMIN_TOC);

    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'nama' => 'Eli Astuti',
        'whatsapp' => '6281399990001',
        'email' => 'eli.admin@example.com',
        'nomor_pendaftaran' => 'TOC-2026-0099',
    ]);

    Livewire::test(ViewPendaftarTeacherOfChampions::class, ['record' => $pendaftar->getKey()])
        ->assertSuccessful()
        ->assertSee('Eli Astuti')
        ->assertSee('6281399990001')
        ->assertSee('eli.admin@example.com');

    expect(AuditLog::query()->where('aksi', 'pendaftar.dilihat')->where('subject_id', $pendaftar->id)->count())->toBe(1);
});

it('masks contacts for panitia-toc on the view page', function () {
    actingAsTocRole(User::ROLE_PANITIA_TOC);

    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6281399990002',
        'email' => 'eli.panitia@example.com',
    ]);

    Livewire::test(ViewPendaftarTeacherOfChampions::class, ['record' => $pendaftar->getKey()])
        ->assertSuccessful()
        ->assertDontSee('6281399990002')
        ->assertDontSee('eli.panitia@example.com')
        ->assertSee(PendaftarTeacherOfChampions::maskContact('6281399990002'))
        ->assertSee(PendaftarTeacherOfChampions::maskContact('eli.panitia@example.com'));
});

it('forbids guests from the admin print page', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create();

    $this->get(route('toc.pendaftar.print', $pendaftar))
        ->assertForbidden();
});

it('prints full contacts for admin-toc and masks them for panitia-toc', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6281399990003',
        'email' => 'eli.print@example.com',
        'nomor_pendaftaran' => 'TOC-2026-0100',
    ]);

    actingAsTocRole(User::ROLE_ADMIN_TOC);

    $this->get(route('toc.pendaftar.print', $pendaftar))
        ->assertOk()
        ->assertSee('TOC-2026-0100')
        ->assertSee('6281399990003')
        ->assertSee('eli.print@example.com');

    expect(AuditLog::query()->where('aksi', 'pendaftar.dicetak')->where('subject_id', $pendaftar->id)->count())->toBe(1);

    actingAsTocRole(User::ROLE_PANITIA_TOC);

    $this->get(route('toc.pendaftar.print', $pendaftar))
        ->assertOk()
        ->assertDontSee('6281399990003')
        ->assertDontSee('eli.print@example.com')
        ->assertSee(PendaftarTeacherOfChampions::maskContact('6281399990003'))
        ->assertSee(PendaftarTeacherOfChampions::maskContact('eli.print@example.com'));
});
