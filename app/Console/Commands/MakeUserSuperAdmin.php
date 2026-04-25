<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeUserSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign super_admin role and grant all permissions to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        // 1. Ensure super_admin role exists
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // 2. Grant ALL permissions to super_admin role
        // This ensures super_admin can do everything, even if seeder missed some
        $permissions = Permission::all();
        $role->syncPermissions($permissions);
        $this->info("Synced " . $permissions->count() . " permissions to super_admin role.");

        // 3. Assign role to user
        $user->assignRole($role);
        
        // 4. Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Success! User {$user->name} ({$user->email}) is now a Super Admin with all permissions.");
    }
}
