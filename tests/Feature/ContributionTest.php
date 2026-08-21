<?php

use App\Actions\Contributions\RecordContributionPaymentAction;
use App\Actions\Meetings\OpenMeetingAction;
use App\Enums\ContributionStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('opening a meeting creates one contribution per active participant', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'contribution_amount' => 40_000,
        'is_active' => true,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'contribution_amount' => 70_000,
        'is_active' => true,
    ]);

    app(OpenMeetingAction::class)
        ->execute($meeting);

    expect(
        $meeting->contributions()->count(),
    )->toBe(2);
});

test('opening a meeting copies participant contribution amount', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'contribution_amount' => 40_000,
        'is_active' => true,
    ]);

    app(OpenMeetingAction::class)
        ->execute($meeting);

    $contribution = Contribution::query()
        ->where('meeting_id', $meeting->id)
        ->where(
            'session_participant_id',
            $participant->id,
        )
        ->firstOrFail();

    expect($contribution->amount_due)
        ->toBe(40_000);
});

test('inactive participant does not receive a contribution', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $activeParticipant =
        SessionParticipant::factory()->create([
            'session_id' => $session->id,
            'contribution_amount' => 40_000,
            'is_active' => true,
        ]);

    $inactiveParticipant =
        SessionParticipant::factory()
        ->inactive()
        ->create([
            'session_id' => $session->id,
            'contribution_amount' => 40_000,
        ]);

    app(OpenMeetingAction::class)
        ->execute($meeting);

    /** @var TestCase $this */
    $this->assertDatabaseHas('contributions', [
        'meeting_id' => $meeting->id,
        'session_participant_id' =>
        $activeParticipant->id,
        'amount_due' => 40_000,
    ]);

    /** @var TestCase $this */
    $this->assertDatabaseMissing('contributions', [
        'meeting_id' => $meeting->id,
        'session_participant_id' =>
        $inactiveParticipant->id,
    ]);
});

test('a contribution starts unpaid', function (): void {
    [$contribution] = contributionForOpenMeeting(
        40_000,
    );

    expect($contribution)
        ->amountPaid()->toBe(0)
        ->remainingAmount()->toBe(40_000)
        ->status()->toBe(
            ContributionStatus::Unpaid,
        );
});

test('a partial contribution payment changes status to partial', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 25_000,
            occurredAt: CarbonImmutable::now(),
        );

    $contribution->refresh();

    expect($contribution)
        ->amountPaid()->toBe(25_000)
        ->remainingAmount()->toBe(15_000)
        ->status()->toBe(
            ContributionStatus::Partial,
        );
});

test('multiple payments can fully settle a contribution', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    $action = app(
        RecordContributionPaymentAction::class,
    );

    $action->execute(
        contribution: $contribution,
        creator: $creator,
        amount: 25_000,
        occurredAt: CarbonImmutable::now(),
    );

    $action->execute(
        contribution: $contribution->refresh(),
        creator: $creator,
        amount: 15_000,
        occurredAt: CarbonImmutable::now(),
    );

    $contribution->refresh();

    expect($contribution)
        ->amountPaid()->toBe(40_000)
        ->remainingAmount()->toBe(0)
        ->status()->toBe(
            ContributionStatus::Paid,
        );

    expect(
        $contribution
            ->transactions()
            ->count(),
    )->toBe(2);
});

test('payment cannot exceed remaining contribution amount', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 40_001,
            occurredAt: CarbonImmutable::now(),
        );
})->throws(ValidationException::class);

test('contribution payment amount must be greater than zero', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 0,
            occurredAt: CarbonImmutable::now(),
        );
})->throws(ValidationException::class);

test('a contribution payment creates a credit transaction', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    $transaction =
        app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 20_000,
            occurredAt: CarbonImmutable::now(),
        );

    expect($transaction)
        ->type->toBe(
            TransactionType::Contribution,
        )
        ->direction->toBe(
            TransactionDirection::Credit,
        )
        ->amount->toBe(20_000);
});

test('transaction belongs to correct session and membership', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    $transaction =
        app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 20_000,
            occurredAt: CarbonImmutable::now(),
        );

    expect($transaction)
        ->session_id->toBe(
            $contribution
                ->meeting
                ->session_id,
        )
        ->membership_id->toBe(
            $contribution
                ->sessionParticipant
                ->membership_id,
        )
        ->created_by->toBe(
            $creator->id,
        );
});

test('transaction is linked polymorphically to the contribution', function (): void {
    [
        $contribution,
        $creator,
    ] = contributionForOpenMeeting(40_000);

    $transaction =
        app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 20_000,
            occurredAt: CarbonImmutable::now(),
        );

    $transactionable =
        $transaction->transactionable;

    expect($transactionable)
        ->toBeInstanceOf(Contribution::class)
        ->id->toBe($contribution->id);
});

test('contribution payment cannot be recorded before meeting opens', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'contribution_amount' => 40_000,
        'is_active' => true,
    ]);

    $contribution = Contribution::factory()
        ->for($meeting)
        ->for(
            $participant,
            'sessionParticipant',
        )
        ->create([
            'amount_due' => 40_000,
        ]);

    $creator = User::factory()->create();

    app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 10_000,
            occurredAt: CarbonImmutable::now(),
        );
})->throws(ValidationException::class);

test('contribution payment cannot be recorded after meeting is completed', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->completed()
        ->create();

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'contribution_amount' => 40_000,
        'is_active' => true,
    ]);

    $contribution = Contribution::factory()
        ->for($meeting)
        ->for(
            $participant,
            'sessionParticipant',
        )
        ->create([
            'amount_due' => 40_000,
        ]);

    $creator = User::factory()->create();

    app(RecordContributionPaymentAction::class)
        ->execute(
            contribution: $contribution,
            creator: $creator,
            amount: 10_000,
            occurredAt: CarbonImmutable::now(),
        );
})->throws(ValidationException::class);

test('contribution keeps amount due when participant contribution amount changes later', function (): void {
    [
        $contribution,
    ] = contributionForOpenMeeting(40_000);

    $participant =
        $contribution->sessionParticipant;

    $participant->update([
        'contribution_amount' => 70_000,
    ]);

    expect(
        $contribution
            ->refresh()
            ->amount_due,
    )->toBe(40_000);
});
