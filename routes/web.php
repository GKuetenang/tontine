<?php

use App\Http\Controllers\MembershipController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TontineController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    /*
     | ------------------------------------------------------------------------------------------------------------------------
     | Routes without Team (tontine_id) context
     | ------------------------------------------------------------------------------------------------------------------------
     */

    Route::resource('tontines', TontineController::class)
        ->only(['index', 'store', 'create']);

    /*
     | ------------------------------------------------------------------------------------------------------------------------
     | Routes with Team (tontine_id) context
     | ------------------------------------------------------------------------------------------------------------------------
     */

    Route::middleware(['tontine.team'])->scopeBindings()->group(function () {
        Route::resource('tontines', TontineController::class)
            ->only(['show', 'edit', 'update', 'destroy']);

        Route::resource('tontines.memberships', MembershipController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->where(['tontine' => '[a-z0-9-]+']);

        Route::resource('tontines.sessions', SessionController::class)
            ->except(['show', 'create'])
            ->where(['tontine' => '[a-z0-9-]+'])
            ->where(['session' => '[a-z0-9-]+']);

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/activate',
            [SessionController::class, 'activate'],
        )->name('tontines.sessions.activate');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/close',
            [SessionController::class, 'close'],
        )->name('tontines.sessions.close');
    });
});

require __DIR__ . '/settings.php';
