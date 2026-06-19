<?php

use App\Enums\Permission;
use App\Http\Controllers\CoordinatorApplicationReviewController;
use App\Http\Controllers\CoordinatorAssignmentAttendanceController;
use App\Http\Controllers\CoordinatorEventController;
use App\Http\Controllers\CoordinatorEventDashboardController;
use App\Http\Controllers\CoordinatorShiftController;
use App\Http\Controllers\CoordinatorZoneController;
use App\Http\Controllers\CrewPerformanceController;
use App\Http\Controllers\CrewShiftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-shifts', [CrewShiftController::class, 'index'])
        ->middleware('can:'.Permission::ViewOwnSchedule->value)
        ->name('crew.shifts.index');
    Route::get('/my-performance', [CrewPerformanceController::class, 'index'])
        ->middleware('can:'.Permission::ViewOwnSchedule->value)
        ->name('crew.performance.index');

    Route::post('/shifts/{shift}/applications', [ShiftApplicationController::class, 'store'])
        ->middleware('can:'.Permission::ApplyForShift->value)
        ->name('shift-applications.store');
    Route::delete('/applications/{application}', [ShiftApplicationController::class, 'destroy'])
        ->middleware('can:'.Permission::CancelApplication->value)
        ->name('shift-applications.destroy');
    Route::patch('/applications/{application}/review', [CoordinatorApplicationReviewController::class, 'update'])
        ->middleware('can:'.Permission::ReviewApplications->value)
        ->name('coordinator.applications.review');
    Route::post('/assignments/{assignment}/check-in', [CoordinatorAssignmentAttendanceController::class, 'checkIn'])
        ->middleware('can:'.Permission::ManageCheckIns->value)
        ->name('coordinator.assignments.check-in');
    Route::post('/assignments/{assignment}/check-out', [CoordinatorAssignmentAttendanceController::class, 'checkOut'])
        ->middleware('can:'.Permission::ManageCheckIns->value)
        ->name('coordinator.assignments.check-out');
    Route::patch('/assignments/{assignment}/no-show', [CoordinatorAssignmentAttendanceController::class, 'updateNoShow'])
        ->middleware('can:'.Permission::MarkNoShows->value)
        ->name('coordinator.assignments.no-show');

    Route::prefix('events')
        ->name('coordinator.events.')
        ->group(function () {
            Route::get('/', [CoordinatorEventController::class, 'index'])
                ->middleware('can:'.Permission::CreateEvents->value)
                ->name('index');
            Route::get('/create', [CoordinatorEventController::class, 'create'])
                ->middleware('can:'.Permission::CreateEvents->value)
                ->name('create');
            Route::post('/', [CoordinatorEventController::class, 'store'])
                ->middleware('can:'.Permission::CreateEvents->value)
                ->name('store');
            Route::get('/{event}/edit', [CoordinatorEventController::class, 'edit'])
                ->middleware('can:'.Permission::EditEvents->value)
                ->name('edit');
            Route::get('/{event}/dashboard', [CoordinatorEventDashboardController::class, 'show'])
                ->middleware('can:'.Permission::EditEvents->value)
                ->name('dashboard');
            Route::post('/{event}/check-ins/scan', [CoordinatorAssignmentAttendanceController::class, 'scan'])
                ->middleware('can:'.Permission::ManageCheckIns->value)
                ->name('check-ins.scan');
            Route::match(['put', 'patch'], '/{event}', [CoordinatorEventController::class, 'update'])
                ->middleware('can:'.Permission::EditEvents->value)
                ->name('update');
            Route::post('/{event}/publish', [CoordinatorEventController::class, 'publish'])
                ->middleware('can:'.Permission::EditEvents->value)
                ->name('publish');
            Route::post('/{event}/zones', [CoordinatorZoneController::class, 'store'])
                ->middleware('can:'.Permission::ManageZones->value)
                ->name('zones.store');
        });

    Route::match(['put', 'patch'], '/zones/{zone}', [CoordinatorZoneController::class, 'update'])
        ->middleware('can:'.Permission::ManageZones->value)
        ->name('coordinator.zones.update');
    Route::delete('/zones/{zone}', [CoordinatorZoneController::class, 'destroy'])
        ->middleware('can:'.Permission::ManageZones->value)
        ->name('coordinator.zones.destroy');

    Route::post('/zones/{zone}/shifts', [CoordinatorShiftController::class, 'store'])
        ->middleware('can:'.Permission::ManageShifts->value)
        ->name('coordinator.shifts.store');
    Route::match(['put', 'patch'], '/shifts/{shift}', [CoordinatorShiftController::class, 'update'])
        ->middleware('can:'.Permission::ManageShifts->value)
        ->name('coordinator.shifts.update');
    Route::delete('/shifts/{shift}', [CoordinatorShiftController::class, 'destroy'])
        ->middleware('can:'.Permission::ManageShifts->value)
        ->name('coordinator.shifts.destroy');
});
