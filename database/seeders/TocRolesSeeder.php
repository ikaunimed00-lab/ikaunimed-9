<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent: only creates the Teacher of Champions permissions and roles.
 * Existing roles (super_admin, admin, ...) and users are left untouched.
 */
class TocRolesSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    public const TOC_PERMISSIONS = [
        'toc.pendaftar.view',
        'toc.pendaftar.update',
        'toc.pendaftar.export',
        'toc.kegiatan.update',
        'toc.dashboard.view',
        'toc.audit.view',
    ];

    /**
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        User::ROLE_ADMIN_TOC => [
            'toc.pendaftar.view',
            'toc.pendaftar.update',
            'toc.pendaftar.export',
            'toc.kegiatan.update',
            'toc.dashboard.view',
            'cms.news.view',
            'cms.news.create',
            'cms.news.edit',
            'cms.news.publish',
        ],
        User::ROLE_PANITIA_TOC => [
            'toc.pendaftar.view',
            'toc.dashboard.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = collect(self::TOC_PERMISSIONS)
            ->merge(collect(self::ROLE_PERMISSIONS)->flatten())
            ->unique();

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $rolePermissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
