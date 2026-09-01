<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Penalties\CreateDefaultPenaltyRulesAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\PenaltyTrigger;
use App\Models\Tontine;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('creates inactive default penalty rules idempotently', function (): void {
    $tontine = Tontine::factory()->create();
    $action = app(CreateDefaultPenaltyRulesAction::class);

    $action->execute($tontine);
    $action->execute($tontine);

    expect($tontine->penaltyRules()->count())->toBe(3)
        ->and($tontine->penaltyRules()->pluck('trigger')->all())
        ->toContain(
            PenaltyTrigger::MeetingLate,
            PenaltyTrigger::MeetingAbsent,
            PenaltyTrigger::ContributionLate,
        )
        ->and($tontine->penaltyRules()->where('is_active', true)->exists())
        ->toBeFalse();
});

it('creates default penalty rules with a new tontine', function (): void {
    app(PermissionSeeder::class)->run();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'Association pénalités',
            'member_number_prefix' => 'AP',
            'default_loan_interest_rate' => '5.00',
            'default_loan_term_months' => 3,
        ])
        ->assertSessionHasNoErrors();

    expect(Tontine::query()->where('user_id', $user->id)->firstOrFail()
        ->penaltyRules()->count())->toBe(3);
});

it('allows the president to configure a penalty rule', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    app(CreateDefaultPenaltyRulesAction::class)->execute($tontine);
    $rule = $tontine->penaltyRules()->where('code', 'meeting_late')->firstOrFail();

    $this->actingAs($president)
        ->put(route('tontines.penalty-rules.update', [$tontine, $rule]), [
            'name' => 'Retard supérieur à quinze minutes',
            'trigger' => PenaltyTrigger::MeetingLate->value,
            'calculation_type' => 'fixed',
            'value' => '1000.00',
            'grace_period' => 15,
            'grace_unit' => 'minutes',
            'is_automatic' => true,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $rule->refresh();

    expect($rule->value)->toBe('1000.00')
        ->and($rule->is_active)->toBeTrue();
});

it('paginates the penalty rules listing', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    app(CreateDefaultPenaltyRulesAction::class)->execute($tontine);

    foreach (range(1, 8) as $number) {
        $tontine->penaltyRules()->create([
            'code' => 'manual_'.$number,
            'name' => 'Pénalité manuelle '.$number,
            'trigger' => PenaltyTrigger::Manual,
            'calculation_type' => 'fixed',
            'is_automatic' => false,
            'is_active' => false,
        ]);
    }

    $this->actingAs($president)
        ->get(route('tontines.penalty-rules.index', [
            'tontine' => $tontine,
            'sort' => 'id',
            'dir' => 'asc',
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('penalty-rules/index')
            ->has('collection.data', 10)
            ->where('collection.data.0.trigger_label', 'Retard à une réunion')
            ->where('collection.data.0.calculation_type_label', 'Montant fixe')
            ->where('collection.data.0.value_label', 'À configurer')
            ->where('collection.data.0.grace_unit_label', 'Minute(s)')
            ->where('collection.data.0.grace_period_label', '15 Minute(s)')
            ->where('collection.data.0.application_label', 'Automatique')
            ->where('collection.data.0.status_label', 'Inactive')
            ->where('collection.current_page', 1)
            ->where('collection.last_page', 2)
            ->where('collection.total', 11));
});

it('sorts penalty rules from the listing query', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    app(CreateDefaultPenaltyRulesAction::class)->execute($tontine);

    $this->actingAs($president)
        ->get(route('tontines.penalty-rules.index', [
            'tontine' => $tontine,
            'sort' => 'name',
            'dir' => 'asc',
        ]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('collection.data.0.name', 'Absence à une réunion')
            ->where('query.sort', 'name')
            ->where('query.dir', 'asc'));
});
