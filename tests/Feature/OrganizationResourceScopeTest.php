<?php

use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('central admin dapat melihat semua organisasi di OrganizationResource query', function () {
    $centralAdmin = User::factory()->create();
    $centralAdmin->assignRole('admin');
    $centralAdmin->organization_id = null;
    $centralAdmin->save();

    $pp = Organization::create([
        'name' => 'PP Org',
        'slug' => 'pp-org-scope',
        'type' => 'pp',
        'is_active' => true,
    ]);

    $dpw = Organization::create([
        'name' => 'DPW Org',
        'slug' => 'dpw-org-scope',
        'type' => 'dpw',
        'parent_id' => $pp->id,
        'is_active' => true,
    ]);

    $dpc = Organization::create([
        'name' => 'DPC Org',
        'slug' => 'dpc-org-scope',
        'type' => 'dpc',
        'parent_id' => $dpw->id,
        'is_active' => true,
    ]);

    $this->actingAs($centralAdmin);

    $ids = OrganizationResource::getEloquentQuery()->pluck('id')->all();

    expect($ids)->toContain($pp->id, $dpw->id, $dpc->id);
});

test('admin yang terikat organisasi hanya melihat organisasi sendiri dan child langsungnya', function () {
    $pp = Organization::create([
        'name' => 'PP Org',
        'slug' => 'pp-org-scope-2',
        'type' => 'pp',
        'is_active' => true,
    ]);

    $dpw = Organization::create([
        'name' => 'DPW Org',
        'slug' => 'dpw-org-scope-2',
        'type' => 'dpw',
        'parent_id' => $pp->id,
        'is_active' => true,
    ]);

    $dpcChild = Organization::create([
        'name' => 'DPC Child',
        'slug' => 'dpc-child-scope',
        'type' => 'dpc',
        'parent_id' => $dpw->id,
        'is_active' => true,
    ]);

    $otherDpc = Organization::create([
        'name' => 'Other DPC',
        'slug' => 'other-dpc-scope',
        'type' => 'dpc',
        'parent_id' => $pp->id,
        'is_active' => true,
    ]);

    $dpwAdmin = User::factory()->create();
    $dpwAdmin->assignRole('admin');
    $dpwAdmin->organization_id = $dpw->id;
    $dpwAdmin->save();

    $this->actingAs($dpwAdmin);

    $ids = OrganizationResource::getEloquentQuery()->pluck('id')->all();

    expect($ids)->toContain($dpw->id, $dpcChild->id)
        ->not->toContain($pp->id, $otherDpc->id);
});

test('user non admin tanpa organization scope tidak melihat organisasi apapun', function () {
    $pp = Organization::create([
        'name' => 'PP Org',
        'slug' => 'pp-org-no-scope',
        'type' => 'pp',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('editor');
    $user->organization_id = null;
    $user->save();

    $this->actingAs($user);

    $ids = OrganizationResource::getEloquentQuery()->pluck('id')->all();

    expect($ids)->toBe([]);
});

