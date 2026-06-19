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
    $crew = $overrides['crew'] ?? User::factory()->create();
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

it('counts total assigned crew as unique crew members not shifts', function () {
    $coordinator = eventDashboardCoordinator();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Crew count event',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Main',
    ]);

    $shiftA = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Shift A',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $shiftB = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Shift B',
        'starts_at' => '2026-08-20 12:00:00',
        'ends_at' => '2026-08-20 16:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $shiftA,
    ]);

    approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $shiftB,
    ]);

    $this->actingAs($coordinator)
        ->get(route('coordinator.events.dashboard', ['event' => $event->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.total_assigned', 1)
            ->where('stats.pending', 1));
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

it('checks in all shifts for the same crew member within one event', function () {
    $coordinator = eventDashboardCoordinator();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

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

    $shiftA = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Ochtendploeg',
        'starts_at' => '2026-08-20 08:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $shiftB = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Middagploeg',
        'starts_at' => '2026-08-20 12:00:00',
        'ends_at' => '2026-08-20 16:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $assignmentA = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $shiftA,
    ]);

    $assignmentB = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $shiftB,
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $assignmentA->check_in_token,
        ])
        ->assertRedirect();

    expect($assignmentA->fresh()->check_in_at)->not->toBeNull();
    expect($assignmentB->fresh()->check_in_at)->not->toBeNull();
    expect($assignmentA->fresh()->check_in_token)->toBe($assignmentB->fresh()->check_in_token);
});

it('uses different qr tokens for the same crew member across different events', function () {
    $coordinator = eventDashboardCoordinator();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $eventA = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Event A',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zoneA = Zone::query()->create([
        'event_id' => $eventA->id,
        'name' => 'Zone A',
    ]);

    $shiftA = Shift::query()->create([
        'zone_id' => $zoneA->id,
        'title' => 'Shift A',
        'starts_at' => '2026-08-20 08:00:00',
        'ends_at' => '2026-08-20 10:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $eventB = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Event B',
        'location' => 'Gent',
        'start_date' => '2026-08-21',
        'end_date' => '2026-08-21',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zoneB = Zone::query()->create([
        'event_id' => $eventB->id,
        'name' => 'Zone B',
    ]);

    $shiftB = Shift::query()->create([
        'zone_id' => $zoneB->id,
        'title' => 'Shift B',
        'starts_at' => '2026-08-21 08:00:00',
        'ends_at' => '2026-08-21 10:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $assignmentA = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $eventA,
        'zone' => $zoneA,
        'shift' => $shiftA,
    ]);

    $assignmentB = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $eventB,
        'zone' => $zoneB,
        'shift' => $shiftB,
    ]);

    expect($assignmentA->check_in_token)->not->toBe($assignmentB->check_in_token);
});

it('checks out a checked-in crew member by scanned qr token', function () {
    $coordinator = eventDashboardCoordinator();
    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Check-out event',
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
        'title' => 'Opbouw',
        'starts_at' => '2026-08-20 06:00:00',
        'ends_at' => '2026-08-20 09:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $assignment = approvedAssignmentForEvent($coordinator, [
        'event' => $event,
        'zone' => $zone,
        'shift' => $shift,
        'assignment' => [
            'check_in_at' => now()->subHours(2),
        ],
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $assignment->check_in_token,
        ])
        ->assertRedirect();

    expect($assignment->fresh()->check_out_at)->not->toBeNull();
});

it('checks out a checked-in crew member manually', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator, [
        'assignment' => [
            'check_in_at' => now()->subHours(2),
        ],
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.assignments.check-out', ['assignment' => $assignment->id]))
        ->assertRedirect();

    expect($assignment->fresh()->check_out_at)->not->toBeNull();
});

it('checks out manually even when crew member still has an active shift', function () {
    $coordinator = eventDashboardCoordinator();

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Actieve shift event',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Main stage',
    ]);

    $activeShift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Actieve shift',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $assignment = approvedAssignmentForEvent($coordinator, [
        'event' => $event,
        'zone' => $zone,
        'shift' => $activeShift,
        'assignment' => [
            'check_in_at' => now()->subHour(),
        ],
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.assignments.check-out', ['assignment' => $assignment->id]))
        ->assertRedirect();

    expect($assignment->fresh()->check_out_at)->not->toBeNull();
});

it('does not check out when crew member still has an active shift', function () {
    $coordinator = eventDashboardCoordinator();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

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

    $activeShift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Ochtendploeg',
        'starts_at' => '2026-08-20 09:00:00',
        'ends_at' => '2026-08-20 12:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $nextShift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Middagploeg',
        'starts_at' => '2026-08-20 12:00:00',
        'ends_at' => '2026-08-20 16:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $activeAssignment = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $activeShift,
        'assignment' => [
            'check_in_at' => now()->subHours(2),
        ],
    ]);

    $targetAssignment = approvedAssignmentForEvent($coordinator, [
        'crew' => $crew,
        'event' => $event,
        'zone' => $zone,
        'shift' => $nextShift,
        'assignment' => [
            'check_in_at' => now()->subHours(2),
        ],
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $targetAssignment->check_in_token,
        ])
        ->assertRedirect();

    expect($targetAssignment->fresh()->check_out_at)->toBeNull();
    expect($activeAssignment->fresh()->check_out_at)->toBeNull();
});

it('allows a new manual check-in after event checkout is completed', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator, [
        'assignment' => [
            'check_in_at' => now()->subHours(3),
            'check_out_at' => now()->subHours(1),
        ],
    ]);

    $this->actingAs($coordinator)
        ->post(route('coordinator.assignments.check-in', ['assignment' => $assignment->id]))
        ->assertRedirect();

    expect($assignment->fresh()->check_in_at?->toIso8601String())
        ->toBe(now()->toIso8601String());
    expect($assignment->fresh()->check_out_at)->toBeNull();
});

it('allows scanning again after event checkout is completed', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator, [
        'assignment' => [
            'check_in_at' => now()->subHours(3),
            'check_out_at' => now()->subHours(1),
        ],
    ]);
    $event = $assignment->shift->zone->event;

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $assignment->check_in_token,
        ])
        ->assertRedirect();

    expect($assignment->fresh()->check_in_at?->toIso8601String())
        ->toBe(now()->toIso8601String());
    expect($assignment->fresh()->check_out_at)->toBeNull();
});

it('does not check in a no-show crew member by scanned qr token', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator, [
        'assignment' => [
            'no_show' => true,
            'no_show_reason' => 'Niet opgedaagd',
            'no_show_marked_by' => $coordinator->id,
        ],
    ]);
    $event = $assignment->shift->zone->event;

    $this->actingAs($coordinator)
        ->post(route('coordinator.events.check-ins.scan', ['event' => $event->id]), [
            'scan_result' => $assignment->check_in_token,
        ])
        ->assertRedirect();

    expect($assignment->fresh()->check_in_at)->toBeNull();
    expect($assignment->fresh()->no_show)->toBeTrue();
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

it('allows marking a checked-in shift as no-show', function () {
    $coordinator = eventDashboardCoordinator();
    $assignment = approvedAssignmentForEvent($coordinator, [
        'assignment' => [
            'check_in_at' => now()->subHour(),
        ],
    ]);

    $this->actingAs($coordinator)
        ->patch(route('coordinator.assignments.no-show', ['assignment' => $assignment->id]), [
            'no_show' => true,
            'reason' => 'Toch niet aanwezig',
        ])
        ->assertRedirect();

    expect($assignment->fresh()->no_show)->toBeTrue();
    expect($assignment->fresh()->no_show_reason)->toBe('Toch niet aanwezig');
});
