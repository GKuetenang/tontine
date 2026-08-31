<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows filtered session transactions with exact totals', function (): void {
    app(PermissionSeeder::class)->run();
    $user = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $user->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $user,
        roleName: 'president',
    );
    $session = Session::factory()->for($tontine)->create();

    Transaction::factory()->for($session)->create([
        'type' => TransactionType::Contribution,
        'direction' => TransactionDirection::Credit,
        'amount' => '20000.50',
    ]);
    Transaction::factory()->for($session)->create([
        'type' => TransactionType::Payout,
        'direction' => TransactionDirection::Debit,
        'amount' => '1250.25',
    ]);

    $this->actingAs($user)
        ->get(route('tontines.sessions.transactions.index', [
            $tontine,
            $session,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('transactions/index')
            ->has('collection.data', 2)
            ->where('transaction_types.1', ['label' => 'Prêt', 'value' => 'loan'])
            ->where('transaction_directions.0', ['label' => 'Crédit', 'value' => 'credit'])
            ->where('summary.credits', '20000.50')
            ->where('summary.debits', '1250.25')
            ->where('summary.balance', '18750.25'));
});

it('does not expose transactions from another session', function (): void {
    app(PermissionSeeder::class)->run();
    $user = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $user->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute(tontine: $tontine, user: $user, roleName: 'president');
    $session = Session::factory()->for($tontine)->create();
    Transaction::factory()->create();

    $this->actingAs($user)
        ->get(route('tontines.sessions.transactions.index', [$tontine, $session]))
        ->assertInertia(fn ($page) => $page->has('collection.data', 0));
});
