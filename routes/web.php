<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContributionPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DrawController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupFinanceController;
use App\Http\Controllers\GroupRoleController;
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
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('account', [AccountController::class, 'index'])->name('account.index');
    Route::get('account/insurance/{group:slug?}', [AccountController::class, 'insurance'])->name('account.insurance.index');
    Route::get('account/contributions', [AccountController::class, 'contributions'])->name('account.contributions.index');
    Route::get('account/loans', [AccountController::class, 'loans'])->name('account.loans.index');

    /*
     | ------------------------------------------------------------------------------------------------------------------------
     | Routes without Team (group_id) context
     | ------------------------------------------------------------------------------------------------------------------------
     */

    Route::resource('groups', GroupController::class)
        ->only(['index', 'store', 'create']);

    /*
     | ------------------------------------------------------------------------------------------------------------------------
     | Routes with Team (group_id) context
     | ------------------------------------------------------------------------------------------------------------------------
     */

    Route::middleware(['group.team'])->scopeBindings()->group(function () {
        Route::resource('groups', GroupController::class)
            ->only(['show', 'edit', 'update', 'destroy']);

        Route::resource('groups.penalty-rules', PenaltyRuleController::class)
            ->only(['index', 'store', 'update'])
            ->scoped([
                'group' => 'slug',
                'penalty_rule' => 'id',
            ]);

        Route::resource('groups.roles', GroupRoleController::class)
            ->only(['index', 'store', 'update'])
            ->scoped(['group' => 'slug', 'role' => 'id']);

        Route::get('groups/{group:slug}/finances', [GroupFinanceController::class, 'index'])
            ->name('groups.finances.index');

        Route::resource('groups.memberships', MembershipController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->scoped([
                'group' => 'slug',
                'membership' => 'id',
            ])
            ->where([
                'group' => '[a-z0-9-]+',
                'membership' => '[0-9]+',
            ]);

        Route::resource('groups.sessions', SessionController::class)
            ->except(['create'])
            ->where(['group' => '[a-z0-9-]+'])
            ->where(['session' => '[a-z0-9-]+']);

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/activate',
            [SessionController::class, 'activate'],
        )->name('groups.sessions.activate');

        Route::get(
            'groups/{group:slug}/sessions/{session:slug}/transactions',
            [TransactionController::class, 'index'],
        )->name('groups.sessions.transactions.index');

        Route::prefix('groups/{group:slug}/sessions/{session:slug}/insurance')
            ->name('groups.sessions.insurance.')
            ->group(function (): void {
                Route::get('/', [InsuranceController::class, 'index'])->name('index');
                Route::post('/', [InsuranceController::class, 'store'])->name('store');
            });

        Route::prefix('groups/{group:slug}/sessions/{session:slug}/donations')
            ->name('groups.sessions.donations.')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [DonationController::class, 'index'])->name('index');
                Route::post('/', [DonationController::class, 'store'])->name('store');
                Route::patch('/{donation}/pay', [DonationController::class, 'pay'])
                    ->whereNumber('donation')->name('pay');
                Route::patch('/{donation}/cancel', [DonationController::class, 'cancel'])
                    ->whereNumber('donation')->name('cancel');
            });

        Route::prefix('groups/{group:slug}/sessions/{session:slug}/loans')
            ->name('groups.sessions.loans.')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [LoanController::class, 'index'])->name('index');
                Route::post('/', [LoanController::class, 'store'])->name('store');
                Route::patch('/{loan}/approve', [LoanController::class, 'approve'])->whereNumber('loan')->name('approve');
                Route::post('/{loan}/repayments', [RepaymentController::class, 'store'])->whereNumber('loan')->name('repayments.store');
            });

        Route::get('groups/{group:slug}/sessions/{session:slug}/repayments', [RepaymentController::class, 'index'])
            ->name('groups.sessions.repayments.index');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/close',
            [SessionController::class, 'close'],
        )->name('groups.sessions.close');

        Route::resource(
            'groups.sessions.participants',
            SessionParticipantController::class,
        )
            ->only([
                'index',
                'store',
                'update',
                'destroy',
            ])
            ->scoped([
                'group' => 'slug',
                'session' => 'slug',
            ])
            ->where([
                'group' => '[a-z0-9-]+',
                'session' => '[a-z0-9-]+',
            ])
            ->whereNumber('participant');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/participants/{participant}/reactivate',
            [SessionParticipantController::class, 'reactivate'],
        )
            ->whereNumber('participant')
            ->name(
                'groups.sessions.participants.reactivate'
            );

        Route::prefix(
            'groups/{group:slug}/sessions/{session:slug}/draw'
        )
            ->name('groups.sessions.draw.')
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
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/draw/swap',
            [
                DrawController::class,
                'swap',
            ],
        )
            ->name(
                'groups.sessions.draw.swap',
            );

        Route::resource(
            'groups.sessions.meetings',
            MeetingController::class,
        )
            ->only([
                'index',
                'show',
                'store',
                'update',
            ])
            ->scoped([
                'group' => 'slug',
                'session' => 'slug',
                'meeting' => 'slug',
            ]);

        Route::post(
            'groups/{group:slug}/sessions/{session:slug}/meeting-schedule',
            [MeetingScheduleController::class, 'store'],
        )->name('groups.sessions.meeting-schedule.store');

        Route::put(
            'groups/{group:slug}/sessions/{session:slug}/meeting-schedule',
            [MeetingScheduleController::class, 'update'],
        )->name('groups.sessions.meeting-schedule.update');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/open',
            [MeetingController::class, 'open'],
        )
            ->name('groups.sessions.meetings.open');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/close',
            [MeetingController::class, 'close'],
        )
            ->name('groups.sessions.meetings.close');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/cancel',
            [MeetingController::class, 'cancel'],
        )
            ->name('groups.sessions.meetings.cancel');

        Route::post(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda',
            [MeetingAgendaItemController::class, 'store'],
        )
            ->name('groups.sessions.meetings.agenda.store');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/reorder',
            [MeetingAgendaItemController::class, 'reorder'],
        )
            ->name('groups.sessions.meetings.agenda.reorder');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/{agendaItem}',
            [MeetingAgendaItemController::class, 'update'],
        )
            ->name('groups.sessions.meetings.agenda.update');

        Route::delete(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/agenda/{agendaItem}',
            [MeetingAgendaItemController::class, 'destroy'],
        )
            ->name('groups.sessions.meetings.agenda.destroy');

        Route::patch(
            'groups/{group:slug}/sessions/{session:slug}/meetings/{meeting:slug}/attendances/{attendance}',
            [MeetingAttendanceController::class, 'update'],
        )
            ->whereNumber('attendance')
            ->name(
                'groups.sessions.meetings.attendances.update'
            );

        Route::get(
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/report',
            [MeetingReportController::class, 'show'],
        )->name(
            'groups.sessions.meetings.report.show'
        );

        Route::post(
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/contributions/{contribution}'
                .'/payments',
            [ContributionPaymentController::class, 'store'],
        )
            ->whereNumber('contribution')
            ->name(
                'groups.sessions.meetings.contributions.payments.store'
            );

        Route::prefix(
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/notes'
        )
            ->name(
                'groups.sessions.meetings.notes.'
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
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/decisions'
        )
            ->name(
                'groups.sessions.meetings.decisions.'
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
            'groups/{group:slug}'
                .'/sessions/{session:slug}'
                .'/meetings/{meeting:slug}'
                .'/payouts',
        )
            ->name(
                'groups.sessions.meetings.payouts.'
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
