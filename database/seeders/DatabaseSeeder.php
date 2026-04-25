<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Organization::firstOrCreate(
            ['slug' => 'pp'],
            [
                'name' => 'Pengurus Pusat',
                'type' => 'pp',
                'is_active' => true,
            ]
        );

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
            SuperAdminSeeder::class, // Ensure Super Admin is created
            CategorySeeder::class,
            LegalizationSeeder::class,
            ShopDemoSeeder::class,
        ]);
    }
}
