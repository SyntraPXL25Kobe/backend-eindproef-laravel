<?php

use App\Http\Controllers\CoordinatorApplicationReviewController;
use App\Http\Controllers\CoordinatorAssignmentAttendanceController;
use App\Http\Controllers\CoordinatorEventController;
use App\Http\Controllers\CoordinatorEventDashboardController;
use App\Http\Controllers\CoordinatorShiftController;
use App\Http\Controllers\CoordinatorZoneController;
use App\Http\Controllers\CrewShiftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\ShiftApplicationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app')->name('home');
Route::inertia('/register/coordinator/pending', 'auth/coordinator-pending')
    ->name('register.coordinator.pending');
Route::get('/events/invite/{token}', [PublicEventController::class, 'showInvite'])
    ->name('events.invite.show');
Route::get('/events/{event}', [PublicEventController::class, 'show'])
    ->name('events.public.show');

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-shifts', [CrewShiftController::class, 'index'])->name('crew.shifts.index');

    Route::post('/shifts/{shift}/applications', [ShiftApplicationController::class, 'store'])
        ->name('shift-applications.store');
    Route::delete('/applications/{application}', [ShiftApplicationController::class, 'destroy'])
        ->name('shift-applications.destroy');
    Route::patch('/applications/{application}/review', [CoordinatorApplicationReviewController::class, 'update'])
        ->name('coordinator.applications.review');
    Route::post('/assignments/{assignment}/check-in', [CoordinatorAssignmentAttendanceController::class, 'checkIn'])
        ->name('coordinator.assignments.check-in');
    Route::patch('/assignments/{assignment}/no-show', [CoordinatorAssignmentAttendanceController::class, 'updateNoShow'])
        ->name('coordinator.assignments.no-show');

    Route::prefix('events')
        ->name('coordinator.events.')
        ->group(function () {
            Route::get('/', [CoordinatorEventController::class, 'index'])->name('index');
            Route::get('/create', [CoordinatorEventController::class, 'create'])->name('create');
            Route::post('/', [CoordinatorEventController::class, 'store'])->name('store');
            Route::get('/{event}/edit', [CoordinatorEventController::class, 'edit'])->name('edit');
            Route::get('/{event}/dashboard', [CoordinatorEventDashboardController::class, 'show'])->name('dashboard');
            Route::post('/{event}/check-ins/scan', [CoordinatorAssignmentAttendanceController::class, 'scan'])->name('check-ins.scan');
            Route::match(['put', 'patch'], '/{event}', [CoordinatorEventController::class, 'update'])->name('update');
            Route::post('/{event}/publish', [CoordinatorEventController::class, 'publish'])->name('publish');
            Route::post('/{event}/zones', [CoordinatorZoneController::class, 'store'])->name('zones.store');
        });

    Route::match(['put', 'patch'], '/zones/{zone}', [CoordinatorZoneController::class, 'update'])
        ->name('coordinator.zones.update');
    Route::delete('/zones/{zone}', [CoordinatorZoneController::class, 'destroy'])
        ->name('coordinator.zones.destroy');

    Route::post('/zones/{zone}/shifts', [CoordinatorShiftController::class, 'store'])
        ->name('coordinator.shifts.store');
    Route::match(['put', 'patch'], '/shifts/{shift}', [CoordinatorShiftController::class, 'update'])
        ->name('coordinator.shifts.update');
    Route::delete('/shifts/{shift}', [CoordinatorShiftController::class, 'destroy'])
        ->name('coordinator.shifts.destroy');
});

require __DIR__.'/settings.php';
