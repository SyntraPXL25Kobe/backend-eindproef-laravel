<?php

use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Skill;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

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