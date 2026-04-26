<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('learner can access learner courses dashboard and see own enrollments', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');
    $learner->forceFill([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => now()->subYears(25)->toDateString(),
        'alamat_lengkap' => 'Alamat Lengkap',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2010',
        's1_tahun_tamat' => '2014',
    ])->save();

    $course = Course::create([
        'title' => 'Public Course',
        'slug' => 'public-course',
        'status' => 'published',
        'is_paid' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
        'started_at' => now(),
        'progress_percentage' => 0,
    ]);

    actingAs($learner);

    $response = get(route('dashboard.elearning.learner.courses.index'));

    $response->assertStatus(200);
});

test('non learner cannot access learner courses dashboard', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    actingAs($subscriber);

    $response = get(route('dashboard.elearning.learner.courses.index'));

    $response->assertRedirect(route('profile.edit'));
});

test('learner can cancel own enrollment from dashboard', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');
    $learner->forceFill([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => now()->subYears(25)->toDateString(),
        'alamat_lengkap' => 'Alamat Lengkap',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2010',
        's1_tahun_tamat' => '2014',
    ])->save();

    $course = Course::create([
        'title' => 'Cancelable Course',
        'slug' => 'cancelable-course',
        'status' => 'published',
        'is_paid' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    $enrollment = Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
        'started_at' => now(),
        'progress_percentage' => 10,
    ]);

    actingAs($learner);

    $response = post(route('dashboard.elearning.enrollments.cancel', $enrollment));

    $response->assertRedirect(route('dashboard.elearning.learner.courses.index'));

    expect($enrollment->fresh()->status)->toBe('cancelled');
});

test('learner dashboard marks premium enrollment as locked for non premium member', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');
    $learner->forceFill([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => now()->subYears(25)->toDateString(),
        'alamat_lengkap' => 'Alamat Lengkap',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2010',
        's1_tahun_tamat' => '2014',
    ])->save();

    $course = Course::create([
        'title' => 'Premium Course',
        'slug' => 'premium-course',
        'status' => 'published',
        'is_paid' => false,
        'requires_premium' => true,
        'created_by' => User::factory()->create()->id,
    ]);

    Enrollment::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'status' => 'active',
        'started_at' => now(),
        'progress_percentage' => 20,
    ]);

    $premiumProduct = Product::create([
        'name' => 'Premium Membership',
        'slug' => 'premium-membership-test',
        'description' => 'Upgrade premium membership',
        'type' => 'service',
        'price' => 150000,
        'is_published' => true,
        'membership_role' => 'premium_member',
    ]);

    actingAs($learner);

    $response = get(route('dashboard.elearning.learner.courses.index'));

    $response->assertOk();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Dashboard/Learner/Courses')
            ->where('enrollments.data.0.enrollment_eligibility.is_locked', true)
            ->where('enrollments.data.0.enrollment_eligibility.reason', 'premium_required')
            ->where('premiumMembershipProduct.slug', $premiumProduct->slug)
    );
});

test('learner with incomplete profile is redirected to profile edit', function () {
    $learner = User::factory()->create();
    $learner->assignRole('learner');

    actingAs($learner);

    $response = get(route('dashboard.elearning.learner.courses.index'));

    $response->assertRedirect(route('profile.edit'));
});

test('subscriber with incomplete profile cannot enroll into course', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $course = Course::create([
        'title' => 'Free Course',
        'slug' => 'free-course-incomplete-profile',
        'status' => 'published',
        'is_paid' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    actingAs($subscriber);

    $response = post(route('courses.enroll', $course));

    $response->assertRedirect(route('profile.edit'));
    expect(Enrollment::where('user_id', $subscriber->id)->where('course_id', $course->id)->exists())->toBeFalse();
});
