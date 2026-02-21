<?php

use App\Models\Scholarship;
use App\Models\ScholarshipApplicant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('redirects guest to login when applying', function () {
    $scholarship = Scholarship::create([
        'title' => 'Beasiswa Test',
        'provider' => 'Yayasan ABC',
        'degree' => 's1',
        'description' => 'Desc',
        'coverage_type' => 'full',
        'status' => 'active',
    ]);

    Storage::fake('public');

    $response = $this->from(route('scholarships.show', $scholarship))
        ->post(route('scholarships.apply', $scholarship), [
            'essay' => str_repeat('a', 40),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

    $response->assertRedirect(route('login'));
});

it('rejects apply when scholarship closed (404)', function () {
    $scholarship = Scholarship::create([
        'title' => 'Beasiswa Closed',
        'provider' => 'Yayasan XYZ',
        'degree' => 's1',
        'description' => 'Desc',
        'coverage_type' => 'full',
        'status' => 'closed',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    Storage::fake('public');

    $response = $this->from(route('scholarships.show', $scholarship))
        ->post(route('scholarships.apply', $scholarship), [
            'essay' => str_repeat('a', 40),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

    $response->assertNotFound();
});

it('accepts a valid application and stores file', function () {
    $scholarship = Scholarship::create([
        'title' => 'Beasiswa OK',
        'provider' => 'Yayasan OK',
        'degree' => 's1',
        'description' => 'Desc',
        'coverage_type' => 'full',
        'status' => 'active',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    Storage::fake('public');

    $response = $this->from(route('scholarships.show', $scholarship))
        ->post(route('scholarships.apply', $scholarship), [
            'essay' => str_repeat('a', 50),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

    $response->assertRedirect(route('scholarships.show', $scholarship));
    $response->assertSessionHas('success');

    expect(ScholarshipApplicant::where('scholarship_id', $scholarship->id)
        ->where('user_id', $user->id)->exists())->toBeTrue();

    $applicant = ScholarshipApplicant::where('scholarship_id', $scholarship->id)
        ->where('user_id', $user->id)->first();
    expect($applicant->status)->toBe('pending');
    Storage::disk('public')->assertExists($applicant->cv_path);
});

it('prevents duplicate applications for same scholarship and user', function () {
    $scholarship = Scholarship::create([
        'title' => 'Beasiswa Duplikat',
        'provider' => 'Yayasan Duplikat',
        'degree' => 's1',
        'description' => 'Desc',
        'coverage_type' => 'full',
        'status' => 'active',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    Storage::fake('public');

    // First apply
    $this->from(route('scholarships.show', $scholarship))
        ->post(route('scholarships.apply', $scholarship), [
            'essay' => str_repeat('a', 50),
            'cv' => UploadedFile::fake()->create('cv1.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('scholarships.show', $scholarship));

    // Second apply should fail
    $resp2 = $this->from(route('scholarships.show', $scholarship))
        ->post(route('scholarships.apply', $scholarship), [
            'essay' => str_repeat('b', 50),
            'cv' => UploadedFile::fake()->create('cv2.pdf', 100, 'application/pdf'),
        ]);

    $resp2->assertRedirect(route('scholarships.show', $scholarship));
    $resp2->assertSessionHasErrors('form');

    // Ensure still only one record
    expect(ScholarshipApplicant::where('scholarship_id', $scholarship->id)
        ->where('user_id', $user->id)->count())->toBe(1);
});
