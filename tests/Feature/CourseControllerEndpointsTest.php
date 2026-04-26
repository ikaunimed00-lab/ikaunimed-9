<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows only published courses on the index endpoint', function () {
    Course::create([
        'title' => 'Published Course',
        'slug' => 'published-course-endpoint',
        'status' => 'published',
        'is_paid' => false,
    ]);

    Course::create([
        'title' => 'Draft Course',
        'slug' => 'draft-course-endpoint',
        'status' => 'draft',
        'is_paid' => false,
    ]);

    $response = $this->get(route('courses.index'));

    $response->assertSuccessful();
    $response->assertSee('Published Course');
    $response->assertDontSee('Draft Course');
});

it('includes enrollment eligibility and premium membership product on course index', function () {
    $user = User::factory()->create([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1990-01-01',
        'alamat_lengkap' => 'Jl. Alumni No. 1',
    ]);
    $user->assignRole('subscriber');

    $premiumCourse = Course::create([
        'title' => 'Premium Locked Course',
        'slug' => 'premium-locked-course-index',
        'status' => 'published',
        'is_paid' => false,
        'requires_premium' => true,
    ]);

    Product::create([
        'name' => 'Premium Membership Index',
        'slug' => 'premium-membership-index',
        'description' => 'Premium membership product',
        'type' => 'service',
        'price' => 125000,
        'is_published' => true,
        'membership_role' => 'premium_member',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('courses.index'));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Course/Index')
            ->where('premiumMembershipProduct.slug', 'premium-membership-index')
            ->where('courses.data.0.slug', $premiumCourse->slug)
            ->where('courses.data.0.enrollment_eligibility.is_locked', true)
            ->where('courses.data.0.enrollment_eligibility.reason', 'premium_required')
    );
});

it('returns not found when guest opens unpublished course detail endpoint', function () {
    $course = Course::create([
        'title' => 'Hidden Course',
        'slug' => 'hidden-course-endpoint',
        'status' => 'draft',
        'is_paid' => false,
    ]);

    $response = $this->get(route('courses.show', $course->slug));

    $response->assertNotFound();
});

it('includes related course enrollment eligibility on course show endpoint', function () {
    $user = User::factory()->create([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1990-01-01',
        'alamat_lengkap' => 'Jl. Alumni No. 1',
    ]);
    $user->assignRole('subscriber');

    $mainCourse = Course::create([
        'title' => 'Main Course Detail',
        'slug' => 'main-course-detail-endpoint',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $relatedCourse = Course::create([
        'title' => 'Related Premium Course',
        'slug' => 'related-premium-course-endpoint',
        'status' => 'published',
        'is_paid' => false,
        'requires_premium' => true,
    ]);

    Product::create([
        'name' => 'Premium Membership Show',
        'slug' => 'premium-membership-show',
        'description' => 'Premium membership product',
        'type' => 'service',
        'price' => 150000,
        'is_published' => true,
        'membership_role' => 'premium_member',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('courses.show', $mainCourse->slug));

    $response->assertSuccessful();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Course/Show')
            ->where('premiumMembershipProduct.slug', 'premium-membership-show')
            ->where('related.0.slug', $relatedCourse->slug)
            ->where('related.0.enrollment_eligibility.is_locked', true)
            ->where('related.0.enrollment_eligibility.reason', 'premium_required')
    );
});

it('enrolls subscriber through course enroll endpoint', function () {
    $user = User::factory()->create([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1990-01-01',
        'alamat_lengkap' => 'Jl. Alumni No. 1',
    ]);
    $user->assignRole('subscriber');

    $course = Course::create([
        'title' => 'Free Course Enrollment',
        'slug' => 'free-course-enrollment-endpoint',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('courses.enroll', $course->slug));

    $response->assertRedirect(route('courses.show', $course->slug));
    $response->assertSessionHas('success', 'Anda berhasil mendaftar course ini.');

    $enrollment = Enrollment::query()
        ->where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment)->not()->toBeNull();
    expect($enrollment->status)->toBe('active');
});
