<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin is redirected to admin panel when visiting dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect('/admin');
});

test('editor is redirected to admin panel when visiting dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect('/admin');
});

test('writer is redirected to admin panel when visiting dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('writer');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect('/admin');
});

test('subscriber with incomplete profile is redirected to profile edit', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect(route('profile.edit'));
});

test('subscriber with complete profile is redirected to subscriber dashboard', function () {
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

    $this->get(route('dashboard'))->assertRedirect(route('dashboard.subscriber'));
});

