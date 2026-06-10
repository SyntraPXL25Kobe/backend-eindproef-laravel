<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Assignment;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;
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
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $volunteerUser = User::updateOrCreate(
            ['email' => 'volunteer@example.com'],
            [
                'name' => 'Volunteer User',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $coordinatorUser = User::updateOrCreate(
            ['email' => 'coordinator@example.com'],
            [
                'name' => 'Coordinator User',
                'phone' => '1122334455',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'coordinator_registration_status' => 'approved',
            ]
        );

        $adminUser->syncRoles(['admin']);
        $volunteerUser->syncRoles(['volunteer']);
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

        $event = Event::updateOrCreate(
            ['title' => 'Zomerfestival 2026', 'coordinator_profile_id' => $coordinatorProfile->id],
            [
                'description' => 'Vrijwilligers ondersteunen onthaal, bar en logistiek.',
                'location' => 'Park Spoor Noord',
                'start_date' => now()->addDays(14)->toDateString(),
                'end_date' => now()->addDays(16)->toDateString(),
                'status' => 'published',
                'max_volunteers' => 80,
            ]
        );

        $welcomeZone = Zone::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Onthaal'],
            [
                'description' => 'Ticketcontrole en bezoekersinformatie.',
            ]
        );

        $barZone = Zone::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Bar'],
            [
                'description' => 'Drankverkoop en stockbeheer.',
            ]
        );

        $morningShift = Shift::updateOrCreate(
            ['zone_id' => $welcomeZone->id, 'title' => 'Onthaal ochtend'],
            [
                'description' => 'Startbriefing, scanning en begeleiding.',
                'starts_at' => now()->addDays(14)->setTime(8, 0, 0),
                'ends_at' => now()->addDays(14)->setTime(12, 0, 0),
                'capacity' => 6,
                'status' => 'open',
            ]
        );

        $eveningShift = Shift::updateOrCreate(
            ['zone_id' => $barZone->id, 'title' => 'Bar avond'],
            [
                'description' => 'Bediening tijdens piekuren.',
                'starts_at' => now()->addDays(14)->setTime(17, 0, 0),
                'ends_at' => now()->addDays(14)->setTime(23, 0, 0),
                'capacity' => 8,
                'status' => 'open',
            ]
        );

        $approvedApplication = Application::updateOrCreate(
            ['shift_id' => $morningShift->id, 'user_id' => $volunteerUser->id],
            [
                'status' => 'approved',
                'motivation' => 'Ik help graag mee met onthaal en bezoekersbegeleiding.',
                'reviewed_by' => $coordinatorUser->id,
                'reviewed_at' => now(),
            ]
        );

        Application::updateOrCreate(
            ['shift_id' => $eveningShift->id, 'user_id' => $volunteerUser->id],
            [
                'status' => 'pending',
                'motivation' => 'Beschikbaar in de avond en ervaring achter de bar.',
            ]
        );

        Assignment::updateOrCreate(
            ['application_id' => $approvedApplication->id],
            [
                'shift_id' => $morningShift->id,
                'user_id' => $volunteerUser->id,
                'confirmed_at' => now(),
                'no_show' => false,
            ]
        );
    }
}
