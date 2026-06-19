<?php

use App\Models\Application;
use App\Models\Assignment;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    Carbon::setTestNow();
});

function crewMember(): User
{
    $user = User::factory()->create();
    $user->syncRoles(['crew']);

    return $user->fresh();
}

it('shows crew shifts chronologically with application statuses', function () {
    Carbon::setTestNow('2026-08-20 10:00:00');

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

    $approvedApplication = Application::query()->create([
        'shift_id' => $laterShift->id,
        'user_id' => $crew->id,
        'status' => 'approved',
    ]);

    Assignment::query()->create([
        'application_id' => $approvedApplication->id,
        'shift_id' => $laterShift->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
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
            ->where('applications.1.status', 'approved')
            ->where('applications.1.check_in.is_available_today', true)
            ->where('applications.1.check_in.no_show', false)
            ->where(
                'applications.1.check_in.qr_svg_src',
                fn ($value) => is_string($value) && str_contains($value, 'data:image'),
            ));
});
