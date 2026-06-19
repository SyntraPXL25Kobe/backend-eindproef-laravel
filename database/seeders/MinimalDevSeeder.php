<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\CoordinatorRegistrationStatus;
use App\Enums\EventStatus;
use App\Enums\EventVisibility;
use App\Enums\ShiftStatus;
use App\Models\Application;
use App\Models\Assignment;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Skill;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MinimalDevSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);

        $skill = Skill::query()->updateOrCreate(
            ['name' => 'Allround'],
            ['description' => 'Basis skill voor snelle development testdata.']
        );

        $admin = $this->upsertUser(
            name: 'Dev Admin',
            email: 'dev.admin@example.com',
            phone: '0472000001'
        );
        $admin->syncRoles(['admin']);

        $coordinator = $this->upsertUser(
            name: 'Dev Coordinator',
            email: 'dev.coordinator@example.com',
            phone: '0472000002',
            coordinatorStatus: CoordinatorRegistrationStatus::Approved
        );
        $coordinator->syncRoles(['coordinator']);

        $crewA = $this->upsertUser(
            name: 'Dev Crew One',
            email: 'dev.crew.one@example.com',
            phone: '0472000003'
        );
        $crewA->syncRoles(['crew']);
        $crewA->skills()->sync([$skill->id]);

        $crewB = $this->upsertUser(
            name: 'Dev Crew Two',
            email: 'dev.crew.two@example.com',
            phone: '0472000004'
        );
        $crewB->syncRoles(['crew']);
        $crewB->skills()->sync([$skill->id]);

        $coordinatorProfile = CoordinatorProfile::query()->updateOrCreate(
            ['user_id' => $coordinator->id],
            [
                'organisation_name' => 'Dev Events',
                'city' => 'Antwerpen',
                'country' => 'Belgie',
            ]
        );

        $event = Event::query()->updateOrCreate(
            [
                'coordinator_profile_id' => $coordinatorProfile->id,
                'title' => 'Dev Test Event',
            ],
            [
                'description' => 'Compact event om snel features te testen.',
                'location' => 'Dev Venue',
                'start_date' => Carbon::today()->toDateString(),
                'end_date' => Carbon::today()->addDay()->toDateString(),
                'status' => EventStatus::Published,
                'publication_visibility' => EventVisibility::Public,
                'published_at' => now(),
                'max_crew_members' => 10,
            ]
        );

        $event->syncPublicationAccess();
        $event->save();

        $zone = Zone::query()->updateOrCreate(
            ['event_id' => $event->id, 'name' => 'General'],
            ['description' => 'Enkele zone voor snelle ontwikkeltests.']
        );

        $shift = Shift::query()->updateOrCreate(
            [
                'zone_id' => $zone->id,
                'title' => 'General Shift',
                'starts_at' => Carbon::today()->setTime(9, 0),
            ],
            [
                'description' => 'Standaard shift voor development.',
                'ends_at' => Carbon::today()->setTime(13, 0),
                'capacity' => 2,
                'required_skill_id' => $skill->id,
                'status' => ShiftStatus::Open,
            ]
        );

        $approvedApplication = Application::query()->updateOrCreate(
            [
                'shift_id' => $shift->id,
                'user_id' => $crewA->id,
            ],
            [
                'status' => ApplicationStatus::Approved,
                'motivation' => 'Snelle approved testapplicatie.',
                'reviewed_by' => $coordinator->id,
                'reviewed_at' => now(),
            ]
        );

        Application::query()->updateOrCreate(
            [
                'shift_id' => $shift->id,
                'user_id' => $crewB->id,
            ],
            [
                'status' => ApplicationStatus::Pending,
                'motivation' => 'Snelle pending testapplicatie.',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        Assignment::query()->updateOrCreate(
            ['application_id' => $approvedApplication->id],
            [
                'shift_id' => $shift->id,
                'user_id' => $crewA->id,
                'confirmed_at' => now(),
                'no_show' => false,
            ]
        );
    }

    private function upsertUser(
        string $name,
        string $email,
        string $phone,
        CoordinatorRegistrationStatus $coordinatorStatus = CoordinatorRegistrationStatus::None,
    ): User {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
                'coordinator_registration_status' => $coordinatorStatus,
            ]
        );
    }
}
