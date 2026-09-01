<?php

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\CalculateSimpleInterestAction;
use App\Actions\Loans\CreateLoanAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Repayments\CreateRepaymentAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\LoanStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function loanContext(): array
{
    $session = Session::factory()->active()->create([
        'start_at' => '2026-01-01',
        'end_at' => '2026-12-31',
    ]);
    $session->tontine()->update([
        'default_loan_interest_rate' => '10.00',
        'default_loan_term_months' => 5,
    ]);
    $membership = Membership::factory()->active()->create(['tontine_id' => $session->tontine_id]);
    SessionParticipant::factory()->for($session)->for($membership)->create();

    return [$session, $membership, User::factory()->create()];
}

it('calculates simple interest on the initial principal without floats', function (): void {
    $amounts = app(CalculateSimpleInterestAction::class)->execute('1250.50', '7.25');

    expect($amounts)->toBe(['interest' => '90.66', 'total' => '1341.16']);
});

it('creates a pending loan whose mandatory due date stays in its session', function (): void {
    [$session, $membership, $user] = loanContext();
    $loan = app(CreateLoanAction::class)->execute($session, $membership, $user, '10000.00', 'Commerce', CarbonImmutable::parse('2026-05-15'));

    expect($loan->status)->toBe(LoanStatus::Pending)
        ->and($loan->interest_amount)->toBe('1000.00')
        ->and($loan->total_due)->toBe('11000.00')
        ->and($loan->term_months)->toBe(5)
        ->and($loan->due_at->toDateString())->toBe('2026-10-15');
});

it('rejects a loan whose due date crosses the session boundary', function (): void {
    [$session, $membership, $user] = loanContext();

    expect(fn () => app(CreateLoanAction::class)->execute($session, $membership, $user, '10000.00', null, CarbonImmutable::parse('2026-09-01')))
        ->toThrow(ValidationException::class);
});

it('approves and disburses a loan with exactly one debit of principal', function (): void {
    [$session, $membership, $user] = loanContext();
    $loan = app(CreateLoanAction::class)->execute($session, $membership, $user, '10000.00', null, CarbonImmutable::parse('2026-05-15'));
    $approved = app(ApproveLoanAction::class)->execute($loan, $user);
    $transaction = Transaction::query()->firstOrFail();

    expect($approved->status)->toBe(LoanStatus::Active)
        ->and($transaction->type)->toBe(TransactionType::Loan)
        ->and($transaction->direction)->toBe(TransactionDirection::Debit)
        ->and($transaction->amount)->toBe('10000.00')
        ->and($transaction->membership_id)->toBe($membership->id);

    expect(fn () => app(ApproveLoanAction::class)->execute($loan, $user))->toThrow(ValidationException::class);
    expect(Transaction::query()->count())->toBe(1);
});

it('creates a loan through the form endpoint and returns it in the listing', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $borrower = User::factory()->create();
    $tontine = Tontine::factory()->create([
        'user_id' => $president->id,
        'default_loan_interest_rate' => '7.50',
        'default_loan_term_months' => 5,
    ]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $membership = app(CreateMembershipAction::class)->execute($tontine, $borrower, 'member');
    $session = Session::factory()->for($tontine)->active()->create([
        'start_at' => now()->subMonth(),
        'end_at' => now()->addYear(),
    ]);
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.loans.store', [$tontine, $session]), [
            'membership_id' => $membership->id,
            'principal_amount' => '20000.00',
            'reason' => 'Développement commercial',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('loans', [
        'session_id' => $session->id,
        'membership_id' => $membership->id,
        'principal_amount' => '20000.00',
        'interest_rate' => '7.50',
        'term_months' => 5,
    ]);

    $loan = $session->loans()->firstOrFail();
    $this->actingAs($president)
        ->patch(route('tontines.sessions.loans.approve', [$tontine, $session, $loan]))
        ->assertRedirect();
    $this->actingAs($president)
        ->post(route('tontines.sessions.loans.repayments.store', [$tontine, $session, $loan]), [
            'amount' => '1000.00',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('repayments', [
        'loan_id' => $loan->id,
        'amount' => '1000.00',
    ]);

    $this->actingAs($president)
        ->get(route('tontines.sessions.repayments.index', [$tontine, $session]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('repayments/index')
            ->has('collection.data', 1)
            ->where('collection.data.0.member_name', $borrower->full_name)
            ->where('collection.data.0.amount', '1000.00'));
});

it('returns a visible loan error when the configured term exceeds the session', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id, 'default_loan_term_months' => 5]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    $membership = app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->active()->create(['start_at' => now()->subMonth(), 'end_at' => now()->addMonth()]);
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.loans.store', [$tontine, $session]), [
            'membership_id' => $membership->id,
            'principal_amount' => '20000.00',
        ])
        ->assertSessionHasErrors('loan');
});

it('records partial repayments as credits and allocates interest before principal', function (): void {
    [$session, $membership, $user] = loanContext();
    $loan = app(CreateLoanAction::class)->execute($session, $membership, $user, '10000.00', null, CarbonImmutable::parse('2026-05-15'));
    app(ApproveLoanAction::class)->execute($loan, $user);

    $first = app(CreateRepaymentAction::class)->execute($loan, $user, '600.00');
    $second = app(CreateRepaymentAction::class)->execute($loan, $user, '1000.00');

    expect($first->interest_amount)->toBe('600.00')
        ->and($first->principal_amount)->toBe('0.00')
        ->and($second->interest_amount)->toBe('400.00')
        ->and($second->principal_amount)->toBe('600.00')
        ->and($loan->fresh()->status)->toBe(LoanStatus::Active)
        ->and(Transaction::query()->where('type', TransactionType::Repayment)->count())->toBe(2);
});

it('marks the loan repaid only when the exact remaining balance is paid', function (): void {
    [$session, $membership, $user] = loanContext();
    $loan = app(CreateLoanAction::class)->execute($session, $membership, $user, '10000.00', null, CarbonImmutable::parse('2026-05-15'));
    app(ApproveLoanAction::class)->execute($loan, $user);
    app(CreateRepaymentAction::class)->execute($loan, $user, '11000.00');

    expect($loan->fresh()->status)->toBe(LoanStatus::Repaid);
    expect(fn () => app(CreateRepaymentAction::class)->execute($loan, $user, '1.00'))->toThrow(ValidationException::class);
});
