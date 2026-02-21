<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin cannot access role governance ui', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/admin/roles')->assertForbidden();
});

test('super admin can access role governance ui', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user);

    $this->get('/admin/roles')->assertOk();
});

