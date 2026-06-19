<?php

use App\Http\Controllers\Auth\CoordinatorRegistrationController;
use App\Http\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register/coordinator', [CoordinatorRegistrationController::class, 'create'])
        ->name('register.coordinator.create');
    Route::post('/register/coordinator', [CoordinatorRegistrationController::class, 'store'])
        ->name('register.coordinator.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/register/coordinator/pending', 'auth/coordinator-pending')
        ->name('register.coordinator.pending');
});

Route::get('/events/invite/{token}', [PublicEventController::class, 'showInvite'])
    ->name('events.invite.show');
Route::get('/events/{event}', [PublicEventController::class, 'show'])
    ->name('events.public.show');

require __DIR__.'/app.php';
require __DIR__.'/settings.php';
