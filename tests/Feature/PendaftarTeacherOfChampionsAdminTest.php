<?php

use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\PendaftarTeacherOfChampionsExporter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('lets admins review registrants', function (string $role) {
    PendaftarTeacherOfChampions::factory()->create(['nama' => 'Guru Teladan']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/admin/pendaftar-teacher-of-champions')
        ->assertOk()
        ->assertSee('Guru Teladan');
})->with(['admin', 'super_admin']);

it('hides registrants from editors', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get('/admin/pendaftar-teacher-of-champions')
        ->assertForbidden();
});

it('filters registrants by alumni UNIMED and summarizes the counts', function () {
    $alumni = PendaftarTeacherOfChampions::factory()->create(['asal_universitas' => 'Universitas Negeri Medan']);
    $nonAlumni = PendaftarTeacherOfChampions::factory()->create(['asal_universitas' => 'USU']);

    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);
    Filament::setCurrentPanel('admin');

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertSee('Total 2 pendaftar · Alumni UNIMED: 1 · Bukan alumni UNIMED: 1')
        ->filterTable('alumni_unimed', true)
        ->assertCanSeeTableRecords([$alumni])
        ->assertCanNotSeeTableRecords([$nonAlumni])
        ->filterTable('alumni_unimed', false)
        ->assertCanSeeTableRecords([$nonAlumni])
        ->assertCanNotSeeTableRecords([$alumni]);
});

it('exports registrants to excel', function () {
    PendaftarTeacherOfChampions::factory()->count(2)->create();

    $response = app(PendaftarTeacherOfChampionsExporter::class)
        ->download(PendaftarTeacherOfChampions::query()->get(), 'xlsx');

    $reader = new Reader;
    $reader->open($response->getFile()->getPathname());

    $rows = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
    }

    $reader->close();

    $alumniColumn = array_search('Alumni UNIMED', $rows[0], true);
    $jenjangColumn = array_search('Jenjang', $rows[0], true);

    expect($rows)->toHaveCount(3)
        ->and($rows[0][0])->toBe('Nomor Pendaftaran')
        ->and($alumniColumn)->not->toBeFalse()
        ->and($rows[1][$alumniColumn])->toBe('Ya')
        ->and($rows[1][$jenjangColumn])->toBe('SMA/MA')
        ->and($response->headers->get('content-disposition'))->toContain('.xlsx');
});
