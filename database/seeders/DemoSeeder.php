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

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);

        $skills = collect([
            ['name' => 'Stagehands', 'description' => 'Opbouw, afbouw en backstage ondersteuning.'],
            ['name' => 'Catering', 'description' => 'Foodstands en hospitality crew.'],
            ['name' => 'Security', 'description' => 'Toegangscontrole en publieksveiligheid.'],
            ['name' => 'EHBO', 'description' => 'Eerste hulp en medische ondersteuning.'],
            ['name' => 'Ticketing', 'description' => 'Scanning en infobalie.'],
        ])->mapWithKeys(fn (array $skill) => [
            $skill['name'] => Skill::query()->updateOrCreate(
                ['name' => $skill['name']],
                ['description' => $skill['description']]
            ),
        ]);

        $admin = $this->upsertUser(
            name: 'Demo Admin',
            email: 'demo.admin@example.com',
            phone: '0470000001'
        );
        $admin->syncRoles(['admin']);

        $coordinator = $this->upsertUser(
            name: 'Sanne Coordinator',
            email: 'sanne.coordinator@example.com',
            phone: '0470000010',
            coordinatorStatus: CoordinatorRegistrationStatus::Approved
        );
        $coordinator->syncRoles(['coordinator']);

        $pendingCoordinator = $this->upsertUser(
            name: 'Liam Pending',
            email: 'liam.pending@example.com',
            phone: '0470000011',
            coordinatorStatus: CoordinatorRegistrationStatus::Pending
        );
        $pendingCoordinator->syncRoles(['coordinator']);

        $coordinatorProfile = CoordinatorProfile::query()->updateOrCreate(
            ['user_id' => $coordinator->id],
            [
                'organisation_name' => 'Summer City Events',
                'vat_number' => 'BE0899123456',
                'address' => 'Parklaan 24',
                'postal_code' => '2000',
                'city' => 'Antwerpen',
                'country' => 'Belgie',
                'website' => 'https://summer-city-events.test',
            ]
        );

        $crew = collect([
            ['name' => 'Nora Crew', 'email' => 'nora.crew@example.com', 'phone' => '0471000001', 'skills' => ['Ticketing']],
            ['name' => 'Milan Crew', 'email' => 'milan.crew@example.com', 'phone' => '0471000002', 'skills' => ['Security']],
            ['name' => 'Yara Crew', 'email' => 'yara.crew@example.com', 'phone' => '0471000003', 'skills' => ['Catering', 'Ticketing']],
            ['name' => 'Omar Crew', 'email' => 'omar.crew@example.com', 'phone' => '0471000004', 'skills' => ['Stagehands']],
            ['name' => 'Lotte Crew', 'email' => 'lotte.crew@example.com', 'phone' => '0471000005', 'skills' => ['EHBO']],
            ['name' => 'Noah Crew', 'email' => 'noah.crew@example.com', 'phone' => '0471000006', 'skills' => ['Security', 'Stagehands']],
        ])->map(function (array $crewMember) use ($skills): User {
            $user = $this->upsertUser(
                name: $crewMember['name'],
                email: $crewMember['email'],
                phone: $crewMember['phone']
            );
            $user->syncRoles(['crew']);
            $user->skills()->sync(
                collect($crewMember['skills'])
                    ->map(fn (string $skillName): int => $skills[$skillName]->id)
                    ->all()
            );

            return $user;
        })->values();

        $event = Event::query()->updateOrCreate(
            [
                'coordinator_profile_id' => $coordinatorProfile->id,
                'title' => 'Summer Beats Festival 2026',
            ],
            [
                'description' => 'Tweedaags stadsfestival met live muziek, foodtrucks en randanimatie.',
                'location' => 'Spoor Noord, Antwerpen',
                'start_date' => Carbon::create(2026, 7, 11)->toDateString(),
                'end_date' => Carbon::create(2026, 7, 12)->toDateString(),
                'status' => EventStatus::Published,
                'publication_visibility' => EventVisibility::Public,
                'max_crew_members' => 80,
                'published_at' => Carbon::create(2026, 6, 25, 10, 0, 0),
            ]
        );

        $event->syncPublicationAccess();
        $event->save();

        $mainStage = Zone::query()->updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Main Stage'],
            ['description' => 'Podium, backstage en artiestenlogistiek.']
        );

        $foodCourt = Zone::query()->updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Food Court'],
            ['description' => 'Drank- en eetstanden met doorlopende service.']
        );

        $entrance = Zone::query()->updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Entrance'],
            ['description' => 'Toegangscontrole en ticket scanning.']
        );

        $shiftSetup = $this->upsertShift(
            zone: $mainStage,
            title: 'Opbouw & soundcheck',
            startsAt: Carbon::create(2026, 7, 11, 8, 0),
            endsAt: Carbon::create(2026, 7, 11, 12, 0),
            capacity: 4,
            requiredSkillId: $skills['Stagehands']->id,
            status: ShiftStatus::Open
        );

        $shiftEvening = $this->upsertShift(
            zone: $mainStage,
            title: 'Evening stage support',
            startsAt: Carbon::create(2026, 7, 11, 18, 0),
            endsAt: Carbon::create(2026, 7, 11, 23, 30),
            capacity: 2,
            requiredSkillId: $skills['Stagehands']->id,
            status: ShiftStatus::Full
        );

        $shiftFood = $this->upsertShift(
            zone: $foodCourt,
            title: 'Lunch service',
            startsAt: Carbon::create(2026, 7, 12, 11, 0),
            endsAt: Carbon::create(2026, 7, 12, 15, 0),
            capacity: 3,
            requiredSkillId: $skills['Catering']->id,
            status: ShiftStatus::Open
        );

        $shiftEntrance = $this->upsertShift(
            zone: $entrance,
            title: 'Gate opening',
            startsAt: Carbon::create(2026, 7, 11, 15, 30),
            endsAt: Carbon::create(2026, 7, 11, 20, 0),
            capacity: 3,
            requiredSkillId: $skills['Ticketing']->id,
            status: ShiftStatus::Closed
        );

        $appApprovedA = $this->upsertApplication(
            shift: $shiftEvening,
            user: $crew[3],
            status: ApplicationStatus::Approved,
            reviewer: $coordinator,
            motivation: 'Ik heb al podiumervaring op meerdere zomerfestivals.'
        );

        $appApprovedB = $this->upsertApplication(
            shift: $shiftEvening,
            user: $crew[5],
            status: ApplicationStatus::Approved,
            reviewer: $coordinator,
            motivation: 'Ervaren in opbouw en snelle wissels backstage.'
        );

        $this->upsertApplication(
            shift: $shiftSetup,
            user: $crew[1],
            status: ApplicationStatus::Pending,
            motivation: 'Beschikbaar vanaf 07:30 en fysiek sterk voor opbouw.'
        );

        $this->upsertApplication(
            shift: $shiftFood,
            user: $crew[2],
            status: ApplicationStatus::Rejected,
            reviewer: $coordinator,
            motivation: 'Ik werk al in foodservice en ben flexibel inzetbaar.'
        );

        $this->upsertApplication(
            shift: $shiftEntrance,
            user: $crew[0],
            status: ApplicationStatus::Cancelled,
            motivation: 'Kan helpen met scanning en bezoekersbegeleiding.'
        );

        Assignment::query()->updateOrCreate(
            ['application_id' => $appApprovedA->id],
            [
                'shift_id' => $appApprovedA->shift_id,
                'user_id' => $appApprovedA->user_id,
                'confirmed_at' => Carbon::create(2026, 7, 5, 14, 0),
                'check_in_at' => Carbon::create(2026, 7, 11, 17, 50),
                'check_out_at' => Carbon::create(2026, 7, 11, 23, 34),
                'no_show' => false,
            ]
        );

        Assignment::query()->updateOrCreate(
            ['application_id' => $appApprovedB->id],
            [
                'shift_id' => $appApprovedB->shift_id,
                'user_id' => $appApprovedB->user_id,
                'confirmed_at' => Carbon::create(2026, 7, 5, 14, 15),
                'no_show' => true,
                'no_show_reason' => 'Laatste-moment afmelding wegens ziekte.',
                'no_show_marked_by' => $coordinator->id,
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

    private function upsertShift(
        Zone $zone,
        string $title,
        Carbon $startsAt,
        Carbon $endsAt,
        int $capacity,
        ?int $requiredSkillId,
        ShiftStatus $status,
    ): Shift {
        return Shift::query()->updateOrCreate(
            [
                'zone_id' => $zone->id,
                'title' => $title,
                'starts_at' => $startsAt,
            ],
            [
                'ends_at' => $endsAt,
                'capacity' => $capacity,
                'required_skill_id' => $requiredSkillId,
                'status' => $status,
                'description' => $title.' - demo shift voor presentatie.',
            ]
        );
    }

    private function upsertApplication(
        Shift $shift,
        User $user,
        ApplicationStatus $status,
        ?User $reviewer = null,
        ?string $motivation = null,
    ): Application {
        return Application::query()->updateOrCreate(
            [
                'shift_id' => $shift->id,
                'user_id' => $user->id,
            ],
            [
                'status' => $status,
                'motivation' => $motivation,
                'reviewed_by' => $reviewer?->id,
                'reviewed_at' => $status === ApplicationStatus::Pending ? null : now(),
            ]
        );
    }
}
