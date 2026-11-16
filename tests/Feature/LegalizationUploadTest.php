<?php

use App\Models\Legalization;
use App\Models\LegalizationFile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function submittedLegalizationFor(User $user): Legalization
{
    return Legalization::create([
        'user_id' => $user->id,
        'jenjang' => 'S1',
        'tahun_lulus' => 2015,
        'jumlah_lembar' => 2,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
}

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

test('alumni can upload an ijazah file and the record has a type', function () {
    $user = User::factory()->create();
    $legalization = submittedLegalizationFor($user);

    $this->actingAs($user)
        ->from(route('legalization.show', $legalization))
        ->post(route('legalization.upload', $legalization), [
            'file' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success')
        ->assertRedirect(route('legalization.show', $legalization));

    $record = LegalizationFile::query()->sole();

    expect($record->legalization_id)->toBe($legalization->id)
        ->and($record->type)->toBe(LegalizationFile::TYPE_IJAZAH)
        ->and($record->original_name)->toBe('ijazah.pdf');

    Storage::disk('local')->assertExists('legalizations/'.$record->filename);
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

test('uploaded file is removed when the record cannot be saved', function () {
    $user = User::factory()->create();
    $legalization = submittedLegalizationFor($user);

    LegalizationFile::creating(function (): void {
        throw new RuntimeException('Insert gagal');
    });

    $this->actingAs($user)
        ->post(route('legalization.upload', $legalization), [
            'file' => UploadedFile::fake()->image('ijazah.jpg'),
        ])
        ->assertServerError();

    expect(LegalizationFile::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('legalizations'))->toBe([]);
});

test('upload is rejected when the legalization is no longer submitted', function () {
    $user = User::factory()->create();
    $legalization = submittedLegalizationFor($user);
    $legalization->update(['status' => 'verified']);

    $this->actingAs($user)
        ->post(route('legalization.upload', $legalization), [
            'file' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf'),
        ])
        ->assertSessionHas('error');

    expect(LegalizationFile::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('legalizations'))->toBe([]);
});

test('alumni cannot upload to another alumni legalization', function () {
    $owner = User::factory()->create();
    $legalization = submittedLegalizationFor($owner);

    $this->actingAs(User::factory()->create())
        ->post(route('legalization.upload', $legalization), [
            'file' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf'),
        ])
        ->assertForbidden();

    expect(LegalizationFile::query()->count())->toBe(0);
});

function storedLegalizationFile(User $owner): LegalizationFile
{
    $legalization = submittedLegalizationFor($owner);
    $path = UploadedFile::fake()->create('ijazah.pdf', 10, 'application/pdf')
        ->store(LegalizationFile::DIRECTORY, LegalizationFile::DISK);

    return LegalizationFile::create([
        'legalization_id' => $legalization->id,
        'type' => LegalizationFile::TYPE_IJAZAH,
        'filename' => basename($path),
        'original_name' => 'ijazah-saya.pdf',
        'mime_type' => 'application/pdf',
        'size' => 10240,
    ]);
}

test('guests cannot download legalization files', function () {
    $file = storedLegalizationFile(User::factory()->create());

    $this->get(route('legalization.files.download', $file))
        ->assertRedirect(route('login'));
});

test('other alumni cannot download someone else legalization file', function () {
    $file = storedLegalizationFile(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->get(route('legalization.files.download', $file))
        ->assertForbidden();
});

test('owner can download their legalization file', function () {
    $owner = User::factory()->create();
    $file = storedLegalizationFile($owner);

    $this->actingAs($owner)
        ->get(route('legalization.files.download', $file))
        ->assertOk()
        ->assertDownload('ijazah-saya.pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('legalization admins can download any legalization file', function (string $role) {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole($role);
    $file = storedLegalizationFile(User::factory()->create());

    $this->actingAs($admin)
        ->get(route('legalization.files.download', $file))
        ->assertOk()
        ->assertDownload('ijazah-saya.pdf');
})->with(['admin', 'super_admin']);

test('download returns not found when the stored file is missing', function () {
    $owner = User::factory()->create();
    $file = storedLegalizationFile($owner);
    Storage::disk(LegalizationFile::DISK)->delete($file->storagePath());

    $this->actingAs($owner)
        ->get(route('legalization.files.download', $file))
        ->assertNotFound();
});

test('legalization detail exposes a download url but not the stored filename', function () {
    $owner = User::factory()->create();
    $file = storedLegalizationFile($owner);

    $this->actingAs($owner)
        ->get(route('legalization.show', $file->legalization_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Subscriber/Legalization/Show')
            ->where('legalization.files.0.download_url', route('legalization.files.download', $file))
            ->missing('legalization.files.0.filename'));
});

test('admin panel lists uploaded legalization files with a download link', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $file = storedLegalizationFile(User::factory()->create());

    $this->actingAs($admin)
        ->get('/admin/legalizations/'.$file->legalization_id)
        ->assertOk()
        ->assertSee('ijazah-saya.pdf')
        ->assertSee(route('legalization.files.download', $file), false);
});
