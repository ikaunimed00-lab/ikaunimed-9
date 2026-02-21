<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('dashboard'))->assertRedirect(route('profile.edit'));
});

test('subscriber with complete profile can access subscriber alumni posts dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->forceFill([
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

    $this->actingAs($user);

    $response = $this->get(route('dashboard.subscriber.alumni-posts.index'));

    $response->assertStatus(200);
});

test('subscriber with incomplete profile is redirected when accessing subscriber alumni posts dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $this->actingAs($user);

    $response = $this->get(route('dashboard.subscriber.alumni-posts.index'));

    $response->assertRedirect(route('profile.edit'));
});

test('subscriber with complete profile can access scholarship applications dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->forceFill([
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

    $this->actingAs($user);

    $response = $this->get(route('dashboard.subscriber.applications.index'));

    $response->assertStatus(200);
});

test('subscriber with incomplete profile is redirected when accessing scholarship applications dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $this->actingAs($user);

    $response = $this->get(route('dashboard.subscriber.applications.index'));

    $response->assertRedirect(route('profile.edit'));
});
