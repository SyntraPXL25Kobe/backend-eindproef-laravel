<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('/register/coordinator/pending', 'auth/coordinator-pending')
    ->name('register.coordinator.pending');

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::inertia('/', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
