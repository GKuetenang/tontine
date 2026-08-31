<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Reports\BuildMeetingReportAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingAttendance;
use App\Models\MeetingDecision;
use App\Models\MeetingNote;
use App\Models\Payout;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds a complete meeting report from loaded meeting data', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->completed()
        ->create();

    $presentParticipant = SessionParticipant::factory()
        ->for($session)
        ->create();

    $absentParticipant = SessionParticipant::factory()
        ->for($session)
        ->create();

    MeetingAgendaItem::factory()
        ->for($meeting)
        ->create();

    MeetingAttendance::factory()
        ->for($meeting)
        ->for(
            $presentParticipant,
            'sessionParticipant',
        )
        ->present()
        ->create();

    MeetingAttendance::factory()
        ->for($meeting)
        ->for(
            $absentParticipant,
            'sessionParticipant',
        )
        ->absent()
        ->create();

    $contribution = Contribution::factory()
        ->for($meeting)
        ->for(
            $presentParticipant,
            'sessionParticipant',
        )
        ->create([
            'amount_due' => 40_000,
        ]);

    Transaction::factory()
        ->for($session)
        ->for($contribution, 'transactionable')
        ->create([
            'type' => TransactionType::Contribution,
            'direction' => TransactionDirection::Credit,
            'amount' => '20000.00',
            'occurred_at' => now(),
        ]);

    Payout::factory()
        ->for($meeting)
        ->paid()
        ->create([
            'amount' => '1250.50',
        ]);

    MeetingNote::factory()
        ->for($meeting)
        ->create();

    MeetingDecision::factory()
        ->for($meeting)
        ->create();

    $report = app(BuildMeetingReportAction::class)
        ->execute($meeting);

    expect($report->meeting->agenda_items)->toHaveCount(1)
        ->and($report->meeting->attendances)->toHaveCount(2)
        ->and($report->meeting->contributions)->toHaveCount(1)
        ->and($report->meeting->payouts)->toHaveCount(1)
        ->and($report->meeting->notes)->toHaveCount(1)
        ->and($report->meeting->decisions)->toHaveCount(1)
        ->and($report->summary->attendances_total)->toBe(2)
        ->and($report->summary->present_total)->toBe(1)
        ->and($report->summary->absent_total)->toBe(1)
        ->and($report->summary->contributions_due)->toBe('40000.00')
        ->and($report->summary->contributions_paid)->toBe('20000.00')
        ->and($report->summary->contributions_remaining)->toBe('20000.00')
        ->and($report->summary->payouts_paid)->toBe('1250.50');
});

it('allows an authorized active member to view a meeting report', function (): void {
    [$user, $tontine, $session, $meeting] = meetingReportAccessContext();

    $this->actingAs($user)
        ->get(route(
            'tontines.sessions.meetings.report.show',
            [$tontine, $session, $meeting],
        ))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('meeting-reports/show')
            ->has('report.meeting')
            ->has('report.summary'));
});

it('forbids a user outside the tontine from viewing a meeting report', function (): void {
    [, $tontine, $session, $meeting] = meetingReportAccessContext();

    $this->actingAs(User::factory()->create())
        ->get(route(
            'tontines.sessions.meetings.report.show',
            [$tontine, $session, $meeting],
        ))
        ->assertForbidden();
});

/**
 * @return array{User, Tontine, Session, Meeting}
 */
function meetingReportAccessContext(): array
{
    app(PermissionSeeder::class)->run();

    $user = User::factory()->create();
    $tontine = Tontine::factory()->create([
        'user_id' => $user->id,
    ]);

    app(CreateDefaultTontineRolesAction::class)
        ->execute($tontine);

    app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $user,
        roleName: 'president',
    );

    $session = Session::factory()
        ->for($tontine)
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->completed()
        ->create();

    return [$user, $tontine, $session, $meeting];
}
