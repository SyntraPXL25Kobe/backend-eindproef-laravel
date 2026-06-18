<?php

use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\User;
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