<?php

use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('guests are redirected away from the dashboard', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('crew users see public published events on the dashboard', function () {
    $user = User::factory()->create();

    $coordinator = User::factory()->create([
        'coordinator_registration_status' => 'approved',
    ]);
    $coordinator->syncRoles(['coordinator']);

    $profile = CoordinatorProfile::query()->create([
        'user_id' => $coordinator->id,
        'organisation_name' => 'Crew Collective',
        'country' => 'Belgie',
    ]);

    Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Publiek Stadsfestival',
        'location' => 'Gent',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-11',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Invite Crew Night',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-12',
        'end_date' => '2026-08-13',
        'status' => 'published',
        'publication_visibility' => 'invite_only',
    ]);

    Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Verborgen Draft Event',
        'location' => 'Brussel',
        'start_date' => '2026-08-14',
        'end_date' => '2026-08-15',
        'status' => 'draft',
        'publication_visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Publiek Stadsfestival')
        ->assertDontSee('Invite Crew Night')
        ->assertDontSee('Verborgen Draft Event');
});

test('dashboard event search filters results via backend query', function () {
    $user = User::factory()->create();

    $coordinator = User::factory()->create([
        'coordinator_registration_status' => 'approved',
    ]);
    $coordinator->syncRoles(['coordinator']);

    $profile = CoordinatorProfile::query()->create([
        'user_id' => $coordinator->id,
        'organisation_name' => 'Crew Collective',
        'country' => 'Belgie',
    ]);

    Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Stadsfestival Gent',
        'location' => 'Gent',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-11',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    Event::query()->create([
        'coordinator_profile_id' => $profile->id,
        'title' => 'Winterbar Antwerpen',
        'location' => 'Antwerpen',
        'start_date' => '2026-08-12',
        'end_date' => '2026-08-13',
        'status' => 'published',
        'publication_visibility' => 'public',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard', ['search' => 'gent']))
        ->assertOk()
        ->assertSee('Stadsfestival Gent')
        ->assertDontSee('Winterbar Antwerpen');
});
