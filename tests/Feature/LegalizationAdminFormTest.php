<?php

use App\Filament\Resources\Legalizations\Pages\CreateLegalization;
use App\Filament\Resources\Legalizations\Pages\EditLegalization;
use App\Models\Legalization;
use App\Models\LegalizationFile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Filament::setCurrentPanel('admin');
    Storage::fake('local');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
});

it('no longer asks for ijazah and transkrip uploads in the admin form', function () {
    Livewire::test(CreateLegalization::class)
        ->assertFormFieldDoesNotExist('file_ijazah')
        ->assertFormFieldDoesNotExist('file_transkrip')
        ->assertFormFieldExists('admin_note');
});

it('saves a legalization status and note without document fields', function () {
    $alumni = User::factory()->create();
    $legalization = Legalization::create([
        'user_id' => $alumni->id,
        'jenjang' => 'S1',
        'tahun_lulus' => 2015,
        'jumlah_lembar' => 2,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    $file = LegalizationFile::create([
        'legalization_id' => $legalization->id,
        'type' => LegalizationFile::TYPE_IJAZAH,
        'filename' => 'legalizations/ijazah.pdf',
        'original_name' => 'ijazah.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    Livewire::test(EditLegalization::class, ['record' => $legalization->getRouteKey()])
        ->fillForm(['status' => 'verified', 'admin_note' => 'Dokumen terbaca jelas'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($legalization->refresh())
        ->status->toBe('verified')
        ->admin_note->toBe('Dokumen terbaca jelas')
        ->and($legalization->files()->pluck('id')->all())->toBe([$file->id]);
});

it('creates a legalization from the admin panel without uploads', function () {
    $alumni = User::factory()->create();

    Livewire::test(CreateLegalization::class)
        ->fillForm([
            'user_id' => $alumni->id,
            'jenjang' => 'S1',
            'tahun_lulus' => 2016,
            'jumlah_lembar' => 3,
            'status' => 'submitted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Legalization::query()->where('user_id', $alumni->id)->sole()->jumlah_lembar)->toBe(3);
});

it('rejects a jenjang longer than the database column', function () {
    Livewire::test(CreateLegalization::class)
        ->fillForm([
            'user_id' => User::factory()->create()->id,
            'jenjang' => 'S1 Pendidikan Ekonomi',
            'tahun_lulus' => 2016,
            'jumlah_lembar' => 1,
            'status' => 'submitted',
        ])
        ->call('create')
        ->assertHasFormErrors(['jenjang' => 'max']);
});
