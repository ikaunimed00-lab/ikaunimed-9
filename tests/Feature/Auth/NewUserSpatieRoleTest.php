<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('newly registered user gets spatie subscriber role', function () {
    $action = app(CreateNewUser::class);

    $user = $action->create([
        'name' => 'Test User',
        'email' => 'test-subscriber@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->hasRole('subscriber'))->toBeTrue();
});

