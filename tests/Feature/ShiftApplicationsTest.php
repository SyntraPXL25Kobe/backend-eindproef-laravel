<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;

const USER_ID_COLUMN = 'user_id';

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function crewUser(): User
{
    $user = User::factory()->create();
    $user->syncRoles(['crew']);

    return $user->fresh();
}

function publishedEventWithZonesAndShifts(): array
{
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
        'title' => 'Zomerfestival',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-11',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $welcomeZone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Onthaal',
    ]);

    $barZone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Bar',
    ]);

    $welcomeShift = Shift::query()->create([
        'zone_id' => $welcomeZone->id,
        'title' => 'Onthaal ochtend',
        'starts_at' => '2026-08-10 08:00:00',
        'ends_at' => '2026-08-10 12:00:00',
        'capacity' => 5,
        'status' => 'open',
    ]);

    $barShift = Shift::query()->create([
        'zone_id' => $barZone->id,
        'title' => 'Bar avond',
        'starts_at' => '2026-08-10 17:00:00',
        'ends_at' => '2026-08-10 22:00:00',
        'capacity' => 4,
        'status' => 'open',
    ]);

    return [$event, $welcomeShift, $barShift];
}

it('allows a crew member to apply for multiple shifts across different zones', function () {
    $user = crewUser();
    [$event, $welcomeShift, $barShift] = publishedEventWithZonesAndShifts();

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $welcomeShift->id]))
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $barShift->id]))
        ->assertRedirect();

    expect(Application::query()->where(USER_ID_COLUMN, $user->id)->count())->toBe(2);

    $this->get(route('events.public.show', ['event' => $event->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/show')
            ->where('event.zones.0.shifts.0.starts_at', '2026-08-10T08:00')
            ->where('event.zones.0.shifts.0.ends_at', '2026-08-10T12:00'))
        ->assertSee('Onthaal')
        ->assertSee('Bar')
        ->assertSee('Onthaal ochtend')
        ->assertSee('Bar avond');
});

it('allows a crew member to cancel a pending application', function () {
    $user = crewUser();
    [, $welcomeShift] = publishedEventWithZonesAndShifts();

    $application = Application::query()->create([
        'shift_id' => $welcomeShift->id,
        USER_ID_COLUMN => $user->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->delete(route('shift-applications.destroy', ['application' => $application->id]))
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Cancelled);
});

it('allows a crew member to re-apply after cancelling an application', function () {
    $user = crewUser();
    [, $welcomeShift] = publishedEventWithZonesAndShifts();

    $application = Application::query()->create([
        'shift_id' => $welcomeShift->id,
        USER_ID_COLUMN => $user->id,
        'status' => 'cancelled',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $welcomeShift->id]), [
            'motivation' => 'Ik ben toch opnieuw beschikbaar.',
        ])
        ->assertRedirect();

    expect(Application::query()->count())->toBe(1);
    expect($application->fresh()->status)->toBe(ApplicationStatus::Pending);
    expect($application->fresh()->motivation)->toBe('Ik ben toch opnieuw beschikbaar.');
    expect($application->fresh()->reviewed_at)->toBeNull();
});

it('does not allow a crew member to re-apply after a rejected application', function () {
    $user = crewUser();
    [, $welcomeShift] = publishedEventWithZonesAndShifts();

    $application = Application::query()->create([
        'shift_id' => $welcomeShift->id,
        USER_ID_COLUMN => $user->id,
        'status' => 'rejected',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $welcomeShift->id]), [
            'motivation' => 'Ik wil toch nog meedoen.',
        ])
        ->assertForbidden();

    expect(Application::query()->count())->toBe(1);
    expect($application->fresh()->status)->toBe(ApplicationStatus::Rejected);
    expect($application->fresh()->motivation)->toBeNull();
});

it('does not allow a crew member to apply for a shift that overlaps with a pending application', function () {
    $user = crewUser();
    [$event] = publishedEventWithZonesAndShifts();

    $zone = Zone::query()->where('event_id', $event->id)->first();

    $shiftA = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Ochtend shift',
        'starts_at' => '2026-08-10 09:00:00',
        'ends_at' => '2026-08-10 13:00:00',
        'capacity' => 3,
        'status' => 'open',
    ]);

    $shiftB = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Overlap shift',
        'starts_at' => '2026-08-10 12:00:00',
        'ends_at' => '2026-08-10 16:00:00',
        'capacity' => 3,
        'status' => 'open',
    ]);

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $shiftA->id]))
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $shiftB->id]))
        ->assertForbidden();

    expect(Application::query()->where(USER_ID_COLUMN, $user->id)->count())->toBe(1);
});

it('allows a crew member to apply for shifts that do not overlap', function () {
    $user = crewUser();
    [$event, $welcomeShift, $barShift] = publishedEventWithZonesAndShifts();

    // welcomeShift: 08:00-12:00, barShift: 17:00-22:00 — no overlap
    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $welcomeShift->id]))
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('shift-applications.store', ['shift' => $barShift->id]))
        ->assertRedirect();

    expect(Application::query()->where(USER_ID_COLUMN, $user->id)->count())->toBe(2);
});
