<?php

use Database\Seeders\TocRolesSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        TocRolesSeeder::seedPanitiaBerkas();
    }

    public function down(): void
    {
        $role = Role::query()
            ->where('name', 'panitia_berkas')
            ->where('guard_name', 'web')
            ->first();

        if ($role) {
            $role->users()->detach();
            $role->syncPermissions([]);
            $role->delete();
        }

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', TocRolesSeeder::PANITIA_BERKAS_PERMISSIONS)
            ->delete();
    }
};
