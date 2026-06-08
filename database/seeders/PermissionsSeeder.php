<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'profile.manage-own',
            'planning.view-own',
            'shifts.view-open',

            'users.view-any',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.manage',
            'permissions.manage',

            'events.view-any',
            'events.view',
            'events.create',
            'events.update',
            'events.publish',
            'events.unpublish',
            'events.delete',

            'zones.view-any',
            'zones.view',
            'zones.create',
            'zones.update',
            'zones.delete',

            'shifts.view-any',
            'shifts.view',
            'shifts.create',
            'shifts.update',
            'shifts.delete',

            'skills.view-any',
            'skills.create',
            'skills.update',
            'skills.delete',
            'skills.manage-own',

            'applications.view-any',
            'applications.create',
            'applications.cancel-own',
            'applications.approve',
            'applications.reject',

            'assignments.view-any',
            'assignments.create',
            'assignments.update',
            'assignments.delete',

            'checkins.view-any',
            'checkins.register',
            'checkins.update',

            'no-shows.view-any',
            'no-shows.mark',
            'no-shows.replace',

            'notifications.send',
            'reports.view-event',
            'reports.view-global',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'admin' => $permissions,
            'coordinator' => [
                'profile.manage-own',
                'planning.view-own',
                'shifts.view-open',

                'events.view-any',
                'events.view',
                'events.create',
                'events.update',
                'events.publish',
                'events.unpublish',

                'zones.view-any',
                'zones.view',
                'zones.create',
                'zones.update',
                'zones.delete',

                'shifts.view-any',
                'shifts.view',
                'shifts.create',
                'shifts.update',
                'shifts.delete',

                'skills.view-any',

                'applications.view-any',
                'applications.approve',
                'applications.reject',

                'assignments.view-any',
                'assignments.create',
                'assignments.update',
                'assignments.delete',

                'checkins.view-any',
                'checkins.register',
                'checkins.update',

                'no-shows.view-any',
                'no-shows.mark',
                'no-shows.replace',

                'notifications.send',
                'reports.view-event',
            ],
            'volunteer' => [
                'profile.manage-own',
                'planning.view-own',
                'shifts.view-open',

                'events.view',
                'zones.view',
                'shifts.view',
                'skills.manage-own',

                'applications.create',
                'applications.cancel-own',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionsForRole) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissionsForRole);
        }

    }
}
