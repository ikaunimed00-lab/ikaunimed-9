<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'cms.news.view',
            'cms.news.create',
            'cms.news.edit',
            'cms.news.publish',
            'cms.scholarship.view',
            'cms.scholarship.create',
            'cms.scholarship.edit',
            'cms.scholarship.publish',
            'cms.job.view',
            'cms.job.create',
            'cms.job.edit',
            'cms.job.publish',
            'cms.legalization.view',
            'cms.legalization.manage',
            'cms.programs.manage',
            'site.homepage.view',
            'site.homepage.edit',
            'site.settings.view',
            'site.settings.edit',
            'elearning.course.view',
            'elearning.course.view_any',
            'elearning.course.view_own',
            'elearning.course.create',
            'elearning.course.edit',
            'elearning.course.edit_own',
            'elearning.course.publish',
            'elearning.course.unpublish',
            'elearning.course.archive',
            'elearning.enrollment.view',
            'elearning.enrollment.view_any',
            'elearning.enrollment.view_own_course',
            'elearning.enrollment.manage',
            'elearning.enrollment.cancel',
            'elearning.participant.enroll',
            'elearning.participant.access_material',
            'elearning.participant.track_progress',
            'alumni.profile.view',
            'alumni.profile.edit',
            'alumni.moderate',
            'community.post.moderate',
            'community.report.handle',
            'master.users.manage',
            'master.organizations.manage',
            'master.organizations.manage_hierarchy',
            'master.roles.manage',
            'portal.profile.edit',
            'portal.enroll.course',
            'portal.enrollment.view_own',
            'shop.product.view',
            'shop.product.manage',
            'shop.product.publish',
            'shop.category.manage',
            'shop.order.view',
            'shop.order.manage',
            'shop.order.refund',
            'shop.order.export',
            'shop.payment.view',
            'shop.settings.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => [
                'master.roles.manage',
            ],
            'admin' => [
                'cms.news.view',
                'cms.news.create',
                'cms.news.edit',
                'cms.news.publish',
                'cms.scholarship.view',
                'cms.scholarship.create',
                'cms.scholarship.edit',
                'cms.scholarship.publish',
                'cms.job.view',
                'cms.job.create',
                'cms.job.edit',
                'cms.job.publish',
                'cms.legalization.view',
                'cms.legalization.manage',
                'cms.programs.manage',
                'site.homepage.view',
                'site.homepage.edit',
                'site.settings.view',
                'site.settings.edit',
                'alumni.profile.view',
                'alumni.profile.edit',
                'alumni.moderate',
                'community.post.moderate',
                'community.report.handle',
                'master.users.manage',
                'master.organizations.manage',
                'shop.product.view',
                'shop.product.manage',
                'shop.product.publish',
                'shop.category.manage',
                'shop.order.view',
                'shop.order.manage',
                'shop.order.refund',
                'shop.order.export',
                'shop.payment.view',
                'shop.settings.manage',
            ],
            'editor' => [
                'cms.news.view',
                'cms.news.create',
                'cms.news.edit',
                'cms.news.publish',
                'cms.scholarship.view',
                'cms.scholarship.create',
                'cms.scholarship.edit',
                'cms.scholarship.publish',
                'cms.job.view',
                'cms.job.create',
                'cms.job.edit',
                'cms.job.publish',
                'cms.legalization.view',
                'cms.legalization.manage',
                'cms.programs.manage',
                'site.homepage.view',
                'site.homepage.edit',
            ],
            'writer' => [
                'cms.news.view',
                'cms.news.create',
                'cms.news.edit',
                'cms.scholarship.view',
                'cms.scholarship.create',
                'cms.scholarship.edit',
                'cms.job.view',
                'cms.job.create',
                'cms.job.edit',
            ],
            'instructor' => [
                'elearning.course.view',
                'elearning.course.view_own',
                'elearning.course.create',
                'elearning.course.edit',
                'elearning.course.edit_own',
                'elearning.course.publish',
                'elearning.course.unpublish',
                'elearning.enrollment.view',
                'elearning.enrollment.view_own_course',
                'elearning.enrollment.manage',
            ],
            'moderator' => [
                'alumni.moderate',
                'community.post.moderate',
                'community.report.handle',
            ],
            'subscriber' => [
                'portal.profile.edit',
                'portal.enroll.course',
                'portal.enrollment.view_own',
            ],
            'learner' => [
                'elearning.participant.enroll',
                'elearning.participant.access_material',
                'elearning.participant.track_progress',
            ],
            'lms_moderator' => [
                'elearning.course.view',
                'elearning.course.view_any',
                'elearning.course.edit',
                'elearning.course.unpublish',
                'elearning.course.archive',
                'elearning.enrollment.view',
                'elearning.enrollment.view_any',
                'elearning.enrollment.manage',
                'elearning.enrollment.cancel',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($superAdminRole) {
            $allPermissionNames = Permission::pluck('name')->toArray();
            $superAdminRole->syncPermissions($allPermissionNames);
        }

        $users = User::whereIn('role', ['admin', 'editor', 'writer', 'subscriber'])->get();

        foreach ($users as $user) {
            $roleName = $user->role;

            if ($roleName === 'admin') {
                $user->assignRole('admin');
            } elseif ($roleName === 'editor') {
                $user->assignRole('editor');
            } elseif ($roleName === 'writer') {
                $user->assignRole('writer');
            } elseif ($roleName === 'subscriber') {
                $user->assignRole('subscriber');
            }
        }
    }
}
