<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan Role super_admin ada
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'admin@ikaunimed.or.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Password default, user harus segera menggantinya
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super_admin');
        
        $this->command->info('User admin@ikaunimed.or.id has been granted super_admin role.');
    }
}
