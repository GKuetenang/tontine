<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Group;
use App\Models\Session;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows filtered session transactions with exact totals', function (): void {
    app(PermissionSeeder::class)->run();
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $user,
        roleName: 'president',
    );
    $session = Session::factory()->for($group)->create();

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
        ->get(route('groups.sessions.transactions.index', [
            $group,
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
    $group = Group::factory()->create(['user_id' => $user->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute(group: $group, user: $user, roleName: 'president');
    $session = Session::factory()->for($group)->create();
    Transaction::factory()->create();

    $this->actingAs($user)
        ->get(route('groups.sessions.transactions.index', [$group, $session]))
        ->assertInertia(fn ($page) => $page->has('collection.data', 0));
});
