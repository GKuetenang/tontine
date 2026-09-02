<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContributionPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DrawController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MeetingAgendaItemController;
use App\Http\Controllers\MeetingAttendanceController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingDecisionController;
use App\Http\Controllers\MeetingNoteController;
use App\Http\Controllers\MeetingReportController;
use App\Http\Controllers\MeetingScheduleController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PenaltyRuleController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SessionParticipantController;
use App\Http\Controllers\TontineController;
use App\Http\Controllers\TontineFinanceController;
use App\Http\Controllers\TontineRoleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('account', [AccountController::class, 'index'])->name('account.index');
    Route::get('account/insurance/{tontine:slug?}', [AccountController::class, 'insurance'])->name('account.insurance.index');
    Route::get('account/contributions', [AccountController::class, 'contributions'])->name('account.contributions.index');
    Route::get('account/loans', [AccountController::class, 'loans'])->name('account.loans.index');

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

        Route::resource('tontines.penalty-rules', PenaltyRuleController::class)
            ->only(['index', 'store', 'update'])
            ->scoped([
                'tontine' => 'slug',
                'penalty_rule' => 'id',
            ]);

        Route::resource('tontines.roles', TontineRoleController::class)
            ->only(['index', 'store', 'update'])
            ->scoped(['tontine' => 'slug', 'role' => 'id']);

        Route::get('tontines/{tontine:slug}/finances', [TontineFinanceController::class, 'index'])
            ->name('tontines.finances.index');

        Route::resource('tontines.memberships', MembershipController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->scoped([
                'tontine' => 'slug',
                'membership' => 'id',
            ])
            ->where([
                'tontine' => '[a-z0-9-]+',
                'membership' => '[0-9]+',
            ]);

        Route::resource('tontines.sessions', SessionController::class)
            ->except(['create'])
            ->where(['tontine' => '[a-z0-9-]+'])
            ->where(['session' => '[a-z0-9-]+']);

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/activate',
            [SessionController::class, 'activate'],
        )->name('tontines.sessions.activate');

        Route::get(
            'tontines/{tontine:slug}/sessions/{session:slug}/transactions',
            [TransactionController::class, 'index'],
        )->name('tontines.sessions.transactions.index');

        Route::prefix('tontines/{tontine:slug}/sessions/{session:slug}/insurance')
            ->name('tontines.sessions.insurance.')
            ->group(function (): void {
                Route::get('/', [InsuranceController::class, 'index'])->name('index');
                Route::post('/', [InsuranceController::class, 'store'])->name('store');
            });

        Route::prefix('tontines/{tontine:slug}/sessions/{session:slug}/donations')
            ->name('tontines.sessions.donations.')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [DonationController::class, 'index'])->name('index');
                Route::post('/', [DonationController::class, 'store'])->name('store');
                Route::patch('/{donation}/pay', [DonationController::class, 'pay'])
                    ->whereNumber('donation')->name('pay');
                Route::patch('/{donation}/cancel', [DonationController::class, 'cancel'])
                    ->whereNumber('donation')->name('cancel');
            });

        Route::prefix('tontines/{tontine:slug}/sessions/{session:slug}/loans')
            ->name('tontines.sessions.loans.')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [LoanController::class, 'index'])->name('index');
                Route::post('/', [LoanController::class, 'store'])->name('store');
                Route::patch('/{loan}/approve', [LoanController::class, 'approve'])->whereNumber('loan')->name('approve');
                Route::post('/{loan}/repayments', [RepaymentController::class, 'store'])->whereNumber('loan')->name('repayments.store');
            });

        Route::get('tontines/{tontine:slug}/sessions/{session:slug}/repayments', [RepaymentController::class, 'index'])
            ->name('tontines.sessions.repayments.index');

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
        Route::patch(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/draw/swap',
            [
                DrawController::class,
                'swap',
            ],
        )
            ->name(
                'tontines.sessions.draw.swap',
            );

        Route::resource(
            'tontines.sessions.meetings',
            MeetingController::class,
        )
            ->only([
                'index',
                'show',
                'store',
                'update',
            ])
            ->scoped([
                'tontine' => 'slug',
                'session' => 'slug',
                'meeting' => 'slug',
            ]);

        Route::post(
            'tontines/{tontine:slug}/sessions/{session:slug}/meeting-schedule',
            [MeetingScheduleController::class, 'store'],
        )->name('tontines.sessions.meeting-schedule.store');

        Route::put(
            'tontines/{tontine:slug}/sessions/{session:slug}/meeting-schedule',
            [MeetingScheduleController::class, 'update'],
        )->name('tontines.sessions.meeting-schedule.update');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/open',
            [MeetingController::class, 'open'],
        )
            ->name('tontines.sessions.meetings.open');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/close',
            [MeetingController::class, 'close'],
        )
            ->name('tontines.sessions.meetings.close');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/cancel',
            [MeetingController::class, 'cancel'],
        )
            ->name('tontines.sessions.meetings.cancel');

        Route::post(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda',
            [MeetingAgendaItemController::class, 'store'],
        )
            ->name('tontines.sessions.meetings.agenda.store');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/reorder',
            [MeetingAgendaItemController::class, 'reorder'],
        )
            ->name('tontines.sessions.meetings.agenda.reorder');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/{agendaItem}',
            [MeetingAgendaItemController::class, 'update'],
        )
            ->name('tontines.sessions.meetings.agenda.update');

        Route::delete(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/{agendaItem}',
            [MeetingAgendaItemController::class, 'destroy'],
        )
            ->name('tontines.sessions.meetings.agenda.destroy');

        Route::patch(
            'tontines/{tontine:slug}/sessions/{session:slug}/meetings/{meeting:slug}/attendances/{attendance}',
            [MeetingAttendanceController::class, 'update'],
        )
            ->whereNumber('attendance')
            ->name(
                'tontines.sessions.meetings.attendances.update'
            );

        Route::get(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/report',
            [MeetingReportController::class, 'show'],
        )->name(
            'tontines.sessions.meetings.report.show'
        );

        Route::post(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/contributions/{contribution}'
                .'/payments',
            [ContributionPaymentController::class, 'store'],
        )
            ->whereNumber('contribution')
            ->name(
                'tontines.sessions.meetings.contributions.payments.store'
            );

        Route::prefix(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/notes'
        )
            ->name(
                'tontines.sessions.meetings.notes.'
            )
            ->scopeBindings()
            ->group(function (): void {
                Route::post(
                    '/',
                    [
                        MeetingNoteController::class,
                        'store',
                    ],
                )->name('store');

                Route::patch(
                    '/{note}',
                    [
                        MeetingNoteController::class,
                        'update',
                    ],
                )
                    ->whereNumber('note')
                    ->name('update');

                Route::delete(
                    '/{note}',
                    [
                        MeetingNoteController::class,
                        'destroy',
                    ],
                )
                    ->whereNumber('note')
                    ->name('destroy');
            });

        Route::prefix(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/decisions'
        )
            ->name(
                'tontines.sessions.meetings.decisions.'
            )
            ->scopeBindings()
            ->group(function (): void {
                Route::post(
                    '/',
                    [
                        MeetingDecisionController::class,
                        'store',
                    ],
                )->name('store');

                Route::patch(
                    '/{decision}',
                    [
                        MeetingDecisionController::class,
                        'update',
                    ],
                )
                    ->whereNumber('decision')
                    ->name('update');

                Route::delete(
                    '/{decision}',
                    [
                        MeetingDecisionController::class,
                        'destroy',
                    ],
                )
                    ->whereNumber('decision')
                    ->name('destroy');
            });

        Route::prefix(
            'tontines/{tontine:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/payouts',
        )
            ->name(
                'tontines.sessions.meetings.payouts.'
            )
            ->scopeBindings()
            ->group(function (): void {
                Route::post(
                    '/',
                    [
                        PayoutController::class,
                        'store',
                    ],
                )->name('store');

                Route::patch(
                    '/{payout}',
                    [
                        PayoutController::class,
                        'update',
                    ],
                )
                    ->whereNumber('payout')
                    ->name('update');

                Route::patch(
                    '/{payout}/pay',
                    [
                        PayoutController::class,
                        'pay',
                    ],
                )
                    ->whereNumber('payout')
                    ->name('pay');

                Route::patch(
                    '/{payout}/cancel',
                    [
                        PayoutController::class,
                        'cancel',
                    ],
                )
                    ->whereNumber('payout')
                    ->name('cancel');
            });
    });
});

require __DIR__.'/settings.php';
