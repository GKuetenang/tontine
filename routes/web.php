<?php

use App\Http\Controllers\DrawController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SessionParticipantController;
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

        Route::resource(
            'tontines.sessions.participants',
            SessionParticipantController::class,
        )
            ->only([
                'index',
                'store',
                'update',
                'destroy',
            ])
            ->scoped([
                'tontine' => 'slug',
                'session' => 'slug',
            ])
            ->where([
                'tontine' => '[a-z0-9-]+',
                'session' => '[a-z0-9-]+',
            ])
            ->whereNumber('participant');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/participants/{participant}/reactivate',
            [SessionParticipantController::class, 'reactivate'],
        )
            ->whereNumber('participant')
            ->name(
                'tontines.sessions.participants.reactivate'
            );

        Route::prefix(
            'tontines/{tontine:slug}/sessions/{session:slug}/draw'
        )
            ->name('tontines.sessions.draw.')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [
                    DrawController::class,
                    'show',
                ])->name('show');

                Route::post('/generate', [
                    DrawController::class,
                    'generate',
                ])->name('generate');

                Route::patch('/confirm', [
                    DrawController::class,
                    'confirm',
                ])->name('confirm');

                Route::patch('/reset', [
                    DrawController::class,
                    'reset',
                ])->name('reset');

                Route::delete('/', [
                    DrawController::class,
                    'destroy',
                ])->name('destroy');

                Route::patch('/restore', [
                    DrawController::class,
                    'restore',
                ])->name('restore');
            });
    });
});

require __DIR__ . '/settings.php';
