<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view open shifts',
            'apply for shift',
            'cancel application',
            'manage own profile',
            'view own schedule',
            'create events',
            'edit events',
            'delete events',
            'manage zones',
            'manage shifts',
            'review applications',
            'manage check-ins',
            'mark no-shows',
            'trigger replacements',
            'view event reports',
            'manage users',
            'manage roles',
            'view global reports',
            'impersonate users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        Role::firstOrCreate(['name' => 'crew', 'guard_name' => 'web'])
            ->syncPermissions([
                'view open shifts',
                'apply for shift',
                'cancel application',
                'manage own profile',
                'view own schedule',
            ]);

        Role::firstOrCreate(['name' => 'coordinator', 'guard_name' => 'web'])
            ->syncPermissions([
                'view open shifts',
                'apply for shift',
                'cancel application',
                'manage own profile',
                'view own schedule',
                'create events',
                'edit events',
                'delete events',
                'manage zones',
                'manage shifts',
                'review applications',
                'manage check-ins',
                'mark no-shows',
                'trigger replacements',
                'view event reports',
            ]);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::pluck('name'));
    }
}
