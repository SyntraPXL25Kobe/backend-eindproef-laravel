<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Assignment;
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

it('shows crew performance grouped per event', function () {
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

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
        'title' => 'Summer Fest',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-21',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Main zone',
    ]);

    $shiftA = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Morning',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 4,
        'status' => 'open',
    ]);

    $shiftB = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Evening',
        'starts_at' => '2026-08-20 17:00:00',
        'ends_at' => '2026-08-20 22:00:00',
        'capacity' => 4,
        'status' => 'open',
    ]);

    $applicationA = Application::query()->create([
        'shift_id' => $shiftA->id,
        'user_id' => $crew->id,
        'status' => ApplicationStatus::Approved,
        'reviewed_by' => $coordinator->id,
        'reviewed_at' => now(),
    ]);

    $applicationB = Application::query()->create([
        'shift_id' => $shiftB->id,
        'user_id' => $crew->id,
        'status' => ApplicationStatus::Approved,
        'reviewed_by' => $coordinator->id,
        'reviewed_at' => now(),
    ]);

    Assignment::query()->create([
        'application_id' => $applicationA->id,
        'shift_id' => $shiftA->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
        'check_in_at' => now()->subDay(),
        'check_out_at' => now()->subDay()->addHours(3),
    ]);

    Assignment::query()->create([
        'application_id' => $applicationB->id,
        'shift_id' => $shiftB->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
        'no_show' => true,
        'no_show_reason' => 'Niet aanwezig',
        'no_show_marked_by' => $coordinator->id,
    ]);

    $this->actingAs($crew)
        ->get(route('crew.performance.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/performance/index')
            ->where('stats.events_total', 1)
            ->where('stats.events_checked_in', 1)
            ->where('stats.total_check_ins', 1)
            ->where('stats.total_no_shows', 1)
            ->has('events', 1)
            ->where('events.0.title', 'Summer Fest')
            ->where('events.0.shifts_total', 2)
            ->has('events.0.shifts', 2)
            ->where('events.0.shifts.0.title', 'Morning')
            ->where('events.0.shifts.1.title', 'Evening')
            ->where('events.0.check_ins', 1)
            ->where('events.0.no_shows', 1));
});
