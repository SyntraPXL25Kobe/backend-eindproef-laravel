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
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->withoutMiddleware(PreventRequestForgery::class);
    Carbon::setTestNow('2026-08-20 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function eventDashboardCoordinator(): User
{
    $user = User::factory()->create([
        'coordinator_registration_status' => 'approved',
    ]);
    $user->syncRoles(['coordinator']);

    CoordinatorProfile::query()->create([
        'user_id' => $user->id,
        'organisation_name' => 'Realtime Crew',
        'country' => 'Belgie',
    ]);

    return $user->fresh();
}

function approvedAssignmentForEvent(User $coordinator, array $overrides = []): Assignment
{
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = $overrides['event'] ?? Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Check-in event',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = $overrides['zone'] ?? Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Ingang',
    ]);

    $shift = $overrides['shift'] ?? Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Welcome crew',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 13:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $application = Application::query()->create([
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'status' => ApplicationStatus::Approved,
        'reviewed_by' => $coordinator->id,
        'reviewed_at' => now(),
    ]);

    return Assignment::query()->create([
        'application_id' => $application->id,
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
        ...($overrides['assignment'] ?? []),
    ]);
}

it('shows the coordinator event dashboard with live attendance stats', function () {
    $coordinator = eventDashboardCoordinator();

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Check-in event',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Ingang',
    ]);

    $shift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Welcome crew',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 13:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $checkedIn = approvedAssignmentForEvent($coordinator, [
        'event' => $event,
        'zone' => $zone,
        'shift' => $shift,
        'assignment' => ['check_in_at' => now()],
    ]);
    $noShow = approvedAssignmentForEvent($coordinator, [
        'event' => $event,
        'zone' => $zone,
        'shift' => $shift,
        'assignment' => [
            'no_show' => true,
            'no_show_reason' => 'Niet opgedaagd',
            'no_show_marked_by' => $coordinator->id,
        ],
    ]);

    $this->actingAs($coordinator)
        ->get(route('coordinator.events.dashboard', ['event' => $event->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/events/dashboard')
            ->where('stats.total_assigned', 2)
            ->where('stats.checked_in', 1)
            ->where('stats.pending', 0)
            ->where('stats.no_shows', 1)
            ->has('assignments', 2));
});

it('checks in a crew member by scanned qr token', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator);
    $event = $assignment->shift->zone->event;

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $assignment->check_in_token,
        ])
        ->assertRedirect();

    expect($assignment->fresh()->check_in_at)->not->toBeNull();
    expect($assignment->fresh()->no_show)->toBeFalse();
});

it('marks and clears a no-show from the coordinator dashboard', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator);

    $this->actingAs($coordinator)
        ->patch(route('coordinator.assignments.no-show', ['assignment' => $assignment->id]), [
            'no_show' => true,
            'reason' => 'Niet aangekomen',
        ])
        ->assertRedirect();

    expect($assignment->fresh()->no_show)->toBeTrue();
    expect($assignment->fresh()->no_show_reason)->toBe('Niet aangekomen');

    $this->actingAs($coordinator)
        ->patch(route('coordinator.assignments.no-show', ['assignment' => $assignment->id]), [
            'no_show' => false,
        ])
        ->assertRedirect();

    expect($assignment->fresh()->no_show)->toBeFalse();
    expect($assignment->fresh()->no_show_reason)->toBeNull();
});
