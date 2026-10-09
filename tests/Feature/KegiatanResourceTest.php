<?php

use App\Models\User;
use Database\Seeders\KegiatanSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, KegiatanSeeder::class]);
});

it('lets admins manage kegiatan from the CMS menu', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/admin/kegiatan')
        ->assertOk()
        ->assertSee('Teacher of Champions');
})->with(['admin', 'super_admin']);

it('hides kegiatan management from editors', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $this->actingAs($user)
        ->get('/admin/kegiatan')
        ->assertForbidden();
});
