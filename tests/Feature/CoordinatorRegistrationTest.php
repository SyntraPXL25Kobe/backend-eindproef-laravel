<?php

use App\Enums\CoordinatorRegistrationStatus;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('guest can view coordinator registration page', function () {
    $this->get(route('register.coordinator.create'))
        ->assertOk();
});

test('guest can submit coordinator registration request', function () {
    $response = $this->post(route('register.coordinator.store'), [
        'name' => 'Coordinator Candidate',
        'email' => 'coordinator@example.com',
        'phone' => '+32 470 12 34 56',
        'organisation_name' => 'Crew Collective',
        'city' => 'Gent',
        'vat_number' => 'BE0123456789',
        'website' => 'https://crewcollective.test',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('register.coordinator.pending'));

    $this->assertDatabaseHas('users', [
        'email' => 'coordinator@example.com',
        'coordinator_registration_status' => CoordinatorRegistrationStatus::Pending->value,
    ]);

    $user = User::query()->sole();

    expect($user)->not->toBeNull();
    expect($user->coordinator_registration_status)->toBe(CoordinatorRegistrationStatus::Pending);
    expect($user->coordinatorProfile)->not->toBeNull();
    expect($user->coordinatorProfile->organisation_name)->toBe('Crew Collective');
});
