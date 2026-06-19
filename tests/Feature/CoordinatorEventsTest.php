<?php

use App\ApplicationStatus;
use App\Models\Application;
use App\Models\Assignment;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Skill;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

const APPLICATION_ID_COLUMN = 'application_id';

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function coordinatorUser(): User
{
    $user = User::factory()->create([
        'coordinator_registration_status' => 'approved',
    ]);
    $user->syncRoles(['coordinator']);

    CoordinatorProfile::query()->create([
        'user_id' => $user->id,
        'organisation_name' => 'Crew Collective',
        'country' => 'Belgie',
    ]);

    return $user->fresh();
}

it('allows a coordinator to create a draft event', function () {
    $user = coordinatorUser();

    $response = $this->actingAs($user)->post('/app/events', [
        'title' => 'Vrijwilligersdag',
        'description' => 'Onboarding en briefing voor nieuwe crew.',
        'location' => 'Gent',
        'start_date' => '2026-07-10',
        'end_date' => '2026-07-11',
        'max_crew_members' => 30,
        'publication_visibility' => 'public',
    ]);

    $event = Event::query()->first();

    $response->assertRedirect(route('coordinator.events.edit', ['event' => $event->getKey()]));

    expect($event)
        ->title->toBe('Vrijwilligersdag')
        ->status->value->toBe('draft')
        ->publication_visibility->value->toBe('public');
});

it('publishes an event publicly', function () {
    $user = coordinatorUser();
    $event = Event::query()->create([
        'coordinator_profile_id' => $user->coordinatorProfile->id,
        'title' => 'Zomerfeest',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-02',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->post(route('coordinator.events.publish', ['event' => $event->getKey()]), [
            'publication_visibility' => 'public',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->getKey()]));

    $event->refresh();

    expect($event->status->value)->toBe('published');
    expect($event->published_at)->not->toBeNull();

    $this->get(route('events.public.show', ['event' => $event->getKey()]))
        ->assertOk()
        ->assertSee('Zomerfeest');
});

it('publishes an event with an invite-only link', function () {
    $user = coordinatorUser();
    $event = Event::query()->create([
        'coordinator_profile_id' => $user->coordinatorProfile->id,
        'title' => 'Crew Night Shift',
        'location' => 'Brussel',
        'start_date' => '2026-09-04',
        'end_date' => '2026-09-05',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->post(route('coordinator.events.publish', ['event' => $event->getKey()]), [
            'publication_visibility' => 'invite_only',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->getKey()]));

    $event->refresh();

    expect($event->status->value)->toBe('published');
    expect($event->publication_visibility->value)->toBe('invite_only');
    expect($event->invite_token)->not->toBeNull();

    $this->get(route('events.public.show', ['event' => $event->getKey()]))->assertNotFound();
    $this->get(route('events.invite.show', ['token' => $event->invite_token]))
        ->assertOk()
        ->assertSee('Crew Night Shift');
});

it('allows a coordinator to manage zones and shifts on an event', function () {
    $user = coordinatorUser();
    $skill = Skill::query()->create([
        'name' => 'Barervaring',
    ]);

    $event = Event::query()->create([
        'coordinator_profile_id' => $user->coordinatorProfile->id,
        'title' => 'Stadsfeest',
        'location' => 'Mechelen',
        'start_date' => '2026-07-20',
        'end_date' => '2026-07-21',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->post(route('coordinator.events.zones.store', ['event' => $event->id]), [
            'name' => 'Barzone',
            'description' => 'Drankverkoop en voorraad.',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->id]));

    $zone = Zone::query()->firstOrFail();

    $this->actingAs($user)
        ->patch(route('coordinator.zones.update', ['zone' => $zone->id]), [
            'name' => 'Hoofdbar',
            'description' => 'Grote centrale bar.',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->id]));

    $this->actingAs($user)
        ->post(route('coordinator.shifts.store', ['zone' => $zone->id]), [
            'title' => 'Bar avond',
            'description' => 'Drankjes serveren.',
            'starts_at' => '2026-07-20 17:00:00',
            'ends_at' => '2026-07-20 23:00:00',
            'capacity' => 6,
            'required_skill_id' => $skill->id,
            'status' => 'open',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->id]));

    $shift = Shift::query()->firstOrFail();

    $this->actingAs($user)
        ->patch(route('coordinator.shifts.update', ['shift' => $shift->id]), [
            'title' => 'Bar late shift',
            'description' => 'Afsluiten en aanvullen.',
            'starts_at' => '2026-07-20 18:00:00',
            'ends_at' => '2026-07-21 00:00:00',
            'capacity' => 4,
            'required_skill_id' => null,
            'status' => 'closed',
        ])
        ->assertRedirect(route('coordinator.events.edit', ['event' => $event->id]));

    expect($zone->fresh()->name)->toBe('Hoofdbar');
    expect($shift->fresh()->title)->toBe('Bar late shift');
    expect($shift->fresh()->status->value)->toBe('closed');
    expect($shift->fresh()->required_skill_id)->toBeNull();
});

it('allows a coordinator to approve a pending application for own event', function () {
    $coordinator = coordinatorUser();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Nachtmarathon',
        'location' => 'Leuven',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-21',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Inkom',
    ]);

    $shift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Late check-in',
        'starts_at' => '2026-08-20 18:00:00',
        'ends_at' => '2026-08-20 22:00:00',
        'capacity' => 2,
        'status' => 'open',
    ]);

    $application = Application::query()->create([
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'status' => 'pending',
        'motivation' => 'Ik ben beschikbaar voor de avondshift.',
    ]);

    $this->actingAs($coordinator)
        ->patch(route('coordinator.applications.review', ['application' => $application->id]), [
            'status' => 'approved',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Approved);
    expect($application->fresh()->reviewed_by)->toBe($coordinator->id);
    expect($application->fresh()->reviewed_at)->not->toBeNull();

    $assignment = Assignment::query()->where(APPLICATION_ID_COLUMN, $application->id)->first();
    expect($assignment)->not->toBeNull();
    expect($assignment?->shift_id)->toBe($shift->id);
    expect($assignment?->user_id)->toBe($crew->id);
    expect($assignment?->confirmed_at)->not->toBeNull();
});

it('does not allow a coordinator to review applications on another coordinators event', function () {
    $eventOwner = coordinatorUser();
    $otherCoordinator = coordinatorUser();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = Event::query()->create([
        'coordinator_profile_id' => $eventOwner->coordinatorProfile->id,
        'title' => 'Crewdag',
        'location' => 'Brugge',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-02',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Backstage',
    ]);

    $shift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Crew briefing',
        'starts_at' => '2026-09-01 10:00:00',
        'ends_at' => '2026-09-01 14:00:00',
        'capacity' => 3,
        'status' => 'open',
    ]);

    $application = Application::query()->create([
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'status' => 'pending',
    ]);

    $this->actingAs($otherCoordinator)
        ->patch(route('coordinator.applications.review', ['application' => $application->id]), [
            'status' => 'rejected',
        ])
        ->assertForbidden();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Pending);
    expect(Assignment::query()->where(APPLICATION_ID_COLUMN, $application->id)->exists())->toBeFalse();
});

it('allows a coordinator to adjust approved and rejected applications', function () {
    $coordinator = coordinatorUser();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Crew Challenge',
        'location' => 'Gent',
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-02',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Main stage',
    ]);

    $shift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Stage support',
        'starts_at' => '2026-10-01 09:00:00',
        'ends_at' => '2026-10-01 14:00:00',
        'capacity' => 2,
        'status' => 'open',
    ]);

    $application = Application::query()->create([
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'status' => 'approved',
        'reviewed_by' => $coordinator->id,
        'reviewed_at' => now(),
    ]);

    Assignment::query()->create([
        'application_id' => $application->id,
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
    ]);

    $this->actingAs($coordinator)
        ->patch(route('coordinator.applications.review', ['application' => $application->id]), [
            'status' => 'rejected',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Rejected);
    expect(Assignment::query()->where(APPLICATION_ID_COLUMN, $application->id)->exists())->toBeFalse();

    $this->actingAs($coordinator)
        ->patch(route('coordinator.applications.review', ['application' => $application->id]), [
            'status' => 'approved',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Approved);
    expect(Assignment::query()->where(APPLICATION_ID_COLUMN, $application->id)->exists())->toBeTrue();
});

it('allows a coordinator to cancel an application so crew can re-apply', function () {
    $coordinator = coordinatorUser();
    $crew = User::factory()->create();
    $crew->syncRoles(['crew']);

    $event = Event::query()->create([
        'coordinator_profile_id' => $coordinator->coordinatorProfile->id,
        'title' => 'Heropen test event',
        'location' => 'Kortrijk',
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-02',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $zone = Zone::query()->create([
        'event_id' => $event->id,
        'name' => 'Service',
    ]);

    $shift = Shift::query()->create([
        'zone_id' => $zone->id,
        'title' => 'Service shift',
        'starts_at' => '2026-11-01 09:00:00',
        'ends_at' => '2026-11-01 13:00:00',
        'capacity' => 2,
        'status' => 'open',
    ]);

    $application = Application::query()->create([
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'status' => 'approved',
        'reviewed_by' => $coordinator->id,
        'reviewed_at' => now(),
    ]);

    Assignment::query()->create([
        'application_id' => $application->id,
        'shift_id' => $shift->id,
        'user_id' => $crew->id,
        'confirmed_at' => now(),
    ]);

    $this->actingAs($coordinator)
        ->patch(route('coordinator.applications.review', ['application' => $application->id]), [
            'status' => 'cancelled',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Cancelled);
    expect(Assignment::query()->where(APPLICATION_ID_COLUMN, $application->id)->exists())->toBeFalse();

    $this->actingAs($crew)
        ->post(route('shift-applications.store', ['shift' => $shift->id]), [
            'motivation' => 'Ik stel me opnieuw kandidaat.',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Pending);
    expect($application->fresh()->reviewed_at)->toBeNull();
});
