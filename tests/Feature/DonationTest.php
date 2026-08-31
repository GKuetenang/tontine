<?php

use App\Actions\Donations\CancelDonationAction;
use App\Actions\Donations\CreateDonationAction;
use App\Actions\Donations\PayDonationAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\DonationStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Donation;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function donationContext(): array
{
    $session = Session::factory()->active()->create();
    $membership = Membership::factory()->active()->create(['tontine_id' => $session->tontine_id]);
    SessionParticipant::factory()->for($session)->for($membership)->create();

    return [$session, $membership, User::factory()->create()];
}

it('creates a pending donation for an active member of the session tontine', function (): void {
    [$session, $membership, $user] = donationContext();
    $donation = app(CreateDonationAction::class)->execute($session, $membership, $user, '1250.50', 'Aide médicale');

    expect($donation->status)->toBe(DonationStatus::Pending)->and($donation->amount)->toBe('1250.50');
});

it('pays a donation with exactly one debit transaction', function (): void {
    [$session, $membership, $user] = donationContext();
    $donation = app(CreateDonationAction::class)->execute($session, $membership, $user, '1250.50', 'Aide médicale');
    $paid = app(PayDonationAction::class)->execute($donation, $user);
    $transaction = Transaction::query()->firstOrFail();

    expect($paid->status)->toBe(DonationStatus::Paid)
        ->and($transaction->type)->toBe(TransactionType::Donation)
        ->and($transaction->direction)->toBe(TransactionDirection::Debit)
        ->and($transaction->amount)->toBe('1250.50')
        ->and($transaction->membership_id)->toBe($membership->id)
        ->and($transaction->session_id)->toBe($session->id);

    expect(fn () => app(PayDonationAction::class)->execute($donation, $user))->toThrow(ValidationException::class);
    expect(Transaction::query()->count())->toBe(1);
});

it('cancels only a pending donation without creating a transaction', function (): void {
    [$session, $membership, $user] = donationContext();
    $donation = app(CreateDonationAction::class)->execute($session, $membership, $user, '5000.00', 'Soutien');
    $cancelled = app(CancelDonationAction::class)->execute($donation);

    expect($cancelled->status)->toBe(DonationStatus::Cancelled)
        ->and(Transaction::query()->count())->toBe(0);
});

it('allows the president to manage donations through the session routes', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $beneficiary = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $membership = app(CreateMembershipAction::class)->execute($tontine, $beneficiary, 'member');
    $session = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.donations.store', [$tontine, $session]), [
            'membership_id' => $membership->id,
            'amount' => '2500.75',
            'reason' => 'Soutien familial',
        ])
        ->assertRedirect();

    $donation = $session->donations()->firstOrFail();

    $this->actingAs($president)
        ->get(route('tontines.sessions.donations.index', [$tontine, $session]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('donations/index')
            ->has('collection.data', 1)
            ->where('collection.data.0.member_name', $beneficiary->name));

    $this->actingAs($president)
        ->patch(route('tontines.sessions.donations.pay', [$tontine, $session, $donation]))
        ->assertRedirect();

    expect($donation->fresh()->status)->toBe(DonationStatus::Paid)
        ->and($donation->transactions()->count())->toBe(1);
});

it('rejects members and donations outside the nested session context', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->active()->create();
    $foreignMembership = Membership::factory()->active()->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.donations.store', [$tontine, $session]), [
            'membership_id' => $foreignMembership->id,
            'amount' => '1000.00',
            'reason' => 'Hors tontine',
        ])
        ->assertNotFound();

    $otherSession = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($otherSession)->for($tontine->memberships()->firstOrFail())->create();
    $donation = Donation::factory()->for($otherSession)->for($tontine->memberships()->firstOrFail())->create([
        'amount' => '1000.00',
        'reason' => 'Autre session',
        'status' => DonationStatus::Pending,
        'created_by' => $president->id,
    ]);

    $this->actingAs($president)
        ->patch(route('tontines.sessions.donations.pay', [$tontine, $session, $donation]))
        ->assertNotFound();
});
