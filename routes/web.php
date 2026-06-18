<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoordinatorEventController;
use App\Http\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('/register/coordinator/pending', 'auth/coordinator-pending')
    ->name('register.coordinator.pending');
Route::get('/events/invite/{token}', [PublicEventController::class, 'showInvite'])
    ->name('events.invite.show');
Route::get('/events/{event}', [PublicEventController::class, 'show'])
    ->name('events.public.show');

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('events')
        ->name('coordinator.events.')
        ->group(function () {
            Route::get('/', [CoordinatorEventController::class, 'index'])->name('index');
            Route::get('/create', [CoordinatorEventController::class, 'create'])->name('create');
            Route::post('/', [CoordinatorEventController::class, 'store'])->name('store');
            Route::get('/{event}/edit', [CoordinatorEventController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{event}', [CoordinatorEventController::class, 'update'])->name('update');
            Route::post('/{event}/publish', [CoordinatorEventController::class, 'publish'])->name('publish');
        });
});

require __DIR__.'/settings.php';
