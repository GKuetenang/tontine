<?php

use App\Http\Controllers\MembershipController;
use App\Http\Controllers\TontineController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::resource('tontines', TontineController::class)->except('show');

    Route::middleware(['tontine.team'])->scopeBindings()->group(function () {
        Route::resource('tontines/{tontine}/memberships', MembershipController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__ . '/settings.php';
