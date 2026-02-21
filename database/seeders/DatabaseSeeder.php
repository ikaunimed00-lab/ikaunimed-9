<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            LegalizationSeeder::class,
            ShopDemoSeeder::class,
        ]);
    }
}
