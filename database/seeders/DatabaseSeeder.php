<?php

namespace Database\Seeders;

use App\Enums\CoordinatorRegistrationStatus;
use App\Models\Application;
use App\Models\CoordinatorProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
        ]);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '1234567890',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $crewUser = User::updateOrCreate(
            ['email' => 'crew@example.com'],
            [
                'name' => 'Crew User',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $coordinatorUser = User::updateOrCreate(
            ['email' => 'coordinator@example.com'],
            [
                'name' => 'Coordinator User',
                'phone' => '1122334455',
                'password' => Hash::make('password'),
                'is_active' => true,
                'coordinator_registration_status' => CoordinatorRegistrationStatus::Pending,
            ]
        );

        $adminUser->syncRoles(['admin']);
        $crewUser->syncRoles(['crew']);
        $coordinatorUser->syncRoles(['coordinator']);

        $coordinatorProfile = CoordinatorProfile::updateOrCreate(
            ['user_id' => $coordinatorUser->id],
            [
                'organisation_name' => 'Community Events vzw',
                'vat_number' => 'BE0123456789',
                'address' => 'Stationsstraat 1',
                'city' => 'Antwerpen',
                'country' => 'Belgie',
            ]
        );
    }
}
