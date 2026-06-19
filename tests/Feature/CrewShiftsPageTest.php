<?php

use App\Models\Application;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

function crewMember(): User
{
    $user = User::factory()->create();
    $user->syncRoles(['crew']);

    return $user->fresh();
}

it('shows crew shifts chronologically with application statuses', function () {
    $crew = crewMember();

    $coordinator = User::factory()->create([
        'coordinator_registration_status' => 'approved',
    ]);
    $coordinator->syncRoles(['coordinator']);

    $profile = CoordinatorProfile::query()->create([
        'user_id' => $coordinator->id,
        'organisation_name' => 'Crew Collective',
        'country' => 'Belgie',
    ]);

    $event = Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Nachtevent',
        'location' => 'Leuven',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-21',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Main zone',
    ]);

    $laterShift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Late shift',
        'starts_at' => '2026-08-20 18:00:00',
        'ends_at' => '2026-08-20 22:00:00',
        'capacity' => 4,
        'status' => 'open',
    ]);

    $earlyShift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Early shift',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 4,
        'status' => 'open',
    ]);

    Application::query()->create([
        'shift_id' => $laterShift->id,
        'user_id' => $crew->id,
        'status' => 'approved',
    ]);

    Application::query()->create([
        'shift_id' => $earlyShift->id,
        'user_id' => $crew->id,
        'status' => 'pending',
        'motivation' => 'Ik ben in de ochtend beschikbaar.',
    ]);

    $this->actingAs($crew)
        ->get(route('crew.shifts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/shifts/index')
            ->has('applications', 2)
            ->where('applications.0.shift.title', 'Early shift')
            ->where('applications.0.status', 'pending')
            ->where('applications.1.shift.title', 'Late shift')
            ->where('applications.1.status', 'approved'));
});
