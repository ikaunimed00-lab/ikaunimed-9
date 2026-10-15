<?php

use App\Models\Legalization;
use App\Models\LegalizationFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    Storage::disk('public')->assertExists('legalizations/'.$record->filename);
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
        ->and(Storage::disk('public')->allFiles('legalizations'))->toBe([]);
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
        ->and(Storage::disk('public')->allFiles('legalizations'))->toBe([]);
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
