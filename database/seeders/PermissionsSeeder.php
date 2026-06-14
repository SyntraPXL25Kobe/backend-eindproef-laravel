<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = array_map(
            static fn (PermissionEnum $permission): string => $permission->value,
            PermissionEnum::cases()
        );

        foreach ($permissions as $permission) {
            SpatiePermission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        Role::firstOrCreate(['name' => 'crew', 'guard_name' => 'web'])
            ->syncPermissions([
                PermissionEnum::ViewOpenShifts->value,
                PermissionEnum::ApplyForShift->value,
                PermissionEnum::CancelApplication->value,
                PermissionEnum::ManageOwnProfile->value,
                PermissionEnum::ViewOwnSchedule->value,
            ]);

        Role::firstOrCreate(['name' => 'coordinator', 'guard_name' => 'web'])
            ->syncPermissions([
                PermissionEnum::ViewOpenShifts->value,
                PermissionEnum::ApplyForShift->value,
                PermissionEnum::CancelApplication->value,
                PermissionEnum::ManageOwnProfile->value,
                PermissionEnum::ViewOwnSchedule->value,
                PermissionEnum::ManageCoordinatorProfile->value,
                PermissionEnum::CreateEvents->value,
                PermissionEnum::EditEvents->value,
                PermissionEnum::DeleteEvents->value,
                PermissionEnum::ManageZones->value,
                PermissionEnum::ManageShifts->value,
                PermissionEnum::ReviewApplications->value,
                PermissionEnum::ManageCheckIns->value,
                PermissionEnum::MarkNoShows->value,
                PermissionEnum::TriggerReplacements->value,
                PermissionEnum::ViewEventReports->value,
            ]);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions(SpatiePermission::query()->pluck('name')->all());
    }
}
