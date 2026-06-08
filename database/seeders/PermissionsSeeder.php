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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // User and access management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.manage',
            'permissions.manage',

            // Event planning domain
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'events.publish',
            'zones.manage',
            'shifts.manage',
            'open_shifts.view',

            // Applications and assignments
            'shift_applications.create',
            'shift_applications.cancel',
            'shift_applications.view',
            'shift_applications.approve',
            'shift_applications.reject',
            'assignments.manage',

            // Event day operations
            'checkins.manage',
            'no_shows.manage',
            'replacements.manage',

            // Personal volunteer scope
            'profile.manage_own',
            'skills.manage_own',
            'planning.view_own',
            'status.view_own',

            // Communication and reporting
            'notifications.view_own',
            'notifications.manage',
            'reports.view_event',
            'reports.view_global',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $volunteerRole = Role::firstOrCreate([
            'name' => 'volunteer',
            'guard_name' => 'web',
        ]);

        $professionalRole = Role::firstOrCreate([
            'name' => 'professional',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $volunteerRole->syncPermissions([
            'open_shifts.view',
            'shift_applications.create',
            'shift_applications.cancel',
            'profile.manage_own',
            'skills.manage_own',
            'planning.view_own',
            'status.view_own',
            'notifications.view_own',
        ]);

        $professionalRole->syncPermissions([
            'events.view',
            'events.create',
            'events.update',
            'events.publish',
            'zones.manage',
            'shifts.manage',
            'open_shifts.view',
            'shift_applications.view',
            'shift_applications.approve',
            'shift_applications.reject',
            'assignments.manage',
            'checkins.manage',
            'no_shows.manage',
            'replacements.manage',
            'notifications.manage',
            'reports.view_event',
        ]);

        $adminRole->syncPermissions(Permission::all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();

    }
}
