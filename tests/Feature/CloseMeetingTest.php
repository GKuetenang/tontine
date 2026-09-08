<?php

use App\Actions\Meetings\CloseMeetingAction;
use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyTrigger;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Membership;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\Session;
use App\Models\SessionParticipant;
use Illuminate\Validation\ValidationException;

test('an in progress meeting can be closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $meeting = app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($meeting)
        ->status->toBe(MeetingStatus::Completed)
        ->closed_at->not->toBeNull();
});

test('pending attendances become absent when meeting is closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $pending = MeetingAttendance::factory()
        ->for($meeting)
        ->create([
            'status' => AttendanceStatus::Pending,
        ]);

    app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($pending->refresh())
        ->status->toBe(AttendanceStatus::Absent)
        ->checked_in_at->toBeNull();
});

test('closing a meeting applies active automatic absence penalties', function (): void {
    $group = Group::factory()->create();
    $session = Session::factory()->for($group)->create();
    $meeting = Meeting::factory()->for($session)->inProgress()->create();
    $membership = Membership::factory()->for($group)->active()->create();
    $participant = SessionParticipant::factory()
        ->for($session)
        ->for($membership)
        ->create();
    MeetingAttendance::factory()
        ->for($meeting)
        ->for($participant, 'sessionParticipant')
        ->create(['status' => AttendanceStatus::Pending]);
    $rule = PenaltyRule::factory()->for($group)->create([
        'trigger' => PenaltyTrigger::MeetingAbsent,
        'value' => '1500.00',
    ]);

    app(CloseMeetingAction::class)->execute($meeting);

    $penalty = Penalty::query()->sole();
    expect($penalty)
        ->penalty_rule_id->toBe($rule->id)
        ->meeting_id->toBe($meeting->id)
        ->membership_id->toBe($membership->id)
        ->trigger->toBe(PenaltyTrigger::MeetingAbsent)
        ->amount->toBe('1500.00')
        ->rule_name->toBe($rule->name)
        ->assessed_at->not->toBeNull();
});

test('closing a meeting ignores inactive and non automatic penalty rules', function (): void {
    $group = Group::factory()->create();
    $session = Session::factory()->for($group)->create();
    $meeting = Meeting::factory()->for($session)->inProgress()->create();
    $membership = Membership::factory()->for($group)->active()->create();
    $participant = SessionParticipant::factory()->for($session)->for($membership)->create();
    MeetingAttendance::factory()->for($meeting)->for($participant, 'sessionParticipant')->absent()->create();
    PenaltyRule::factory()->for($group)->create(['is_active' => false]);
    PenaltyRule::factory()->for($group)->create(['code' => 'manual-disabled', 'is_automatic' => false]);

    app(CloseMeetingAction::class)->execute($meeting);

    expect(Penalty::query()->count())->toBe(0);
});

test('an automatic percentage penalty is calculated from the contribution due', function (): void {
    $group = Group::factory()->create();
    $session = Session::factory()->for($group)->create();
    $meeting = Meeting::factory()->for($session)->inProgress()->create();
    $membership = Membership::factory()->for($group)->active()->create();
    $participant = SessionParticipant::factory()->for($session)->for($membership)->create();
    $contribution = Contribution::factory()
        ->for($meeting)
        ->for($participant, 'sessionParticipant')
        ->create(['amount_due' => 40_000]);
    PenaltyRule::factory()->for($group)->create([
        'trigger' => PenaltyTrigger::ContributionIncomplete,
        'calculation_type' => PenaltyCalculationType::Percentage,
        'value' => '5.25',
    ]);

    app(CloseMeetingAction::class)->execute($meeting);

    expect(Penalty::query()->sole())
        ->contribution_id->toBe($contribution->id)
        ->amount->toBe('2100.00');
});

test('existing attendance statuses are preserved when meeting is closed', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $present = MeetingAttendance::factory()
        ->for($meeting)
        ->present()
        ->create();

    $late = MeetingAttendance::factory()
        ->for($meeting)
        ->late()
        ->create();

    $excused = MeetingAttendance::factory()
        ->for($meeting)
        ->excused()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);

    expect($present->refresh()->status)
        ->toBe(AttendanceStatus::Present)
        ->and($late->refresh()->status)
        ->toBe(AttendanceStatus::Late)
        ->and($excused->refresh()->status)
        ->toBe(AttendanceStatus::Excused);
});

test('a scheduled meeting cannot be closed', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a completed meeting cannot be closed again', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a cancelled meeting cannot be closed', function (): void {
    $meeting = Meeting::factory()
        ->cancelled()
        ->create();

    app(CloseMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);
