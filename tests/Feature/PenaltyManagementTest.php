<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\GroupRole;
use App\Enums\PenaltyStatus;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function penaltyManagementContext(): array
{
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute($group, $president, GroupRole::President->value);
    $membership = app(CreateMembershipAction::class)->execute($group, $member, GroupRole::Member->value);
    $session = Session::factory()->for($group)->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();
    $meeting = Meeting::factory()->for($session)->create();
    $rule = PenaltyRule::factory()->for($group)->create();

    return [$president, $group, $session, $membership, $meeting, $rule];
}

it('allows an authorized manager to add a manual penalty', function (): void {
    [$president, $group, $session, $membership, $meeting, $rule] = penaltyManagementContext();

    $this->actingAs($president)
        ->post(route('groups.sessions.penalties.store', [$group, $session]), [
            'membership_id' => $membership->id,
            'meeting_id' => $meeting->id,
            'penalty_rule_id' => $rule->id,
            'amount' => '2500.00',
            'reason' => 'Non-respect du règlement',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Penalty::query()->sole())
        ->source->toBe('manual')
        ->status->toBe(PenaltyStatus::Pending)
        ->amount->toBe('2500.00')
        ->reason->toBe('Non-respect du règlement')
        ->created_by->toBe($president->id);
});

it('records who waived a penalty and why', function (): void {
    [$president, $group, $session, $membership, $meeting, $rule] = penaltyManagementContext();
    $penalty = Penalty::factory()->create([
        'penalty_rule_id' => $rule->id,
        'meeting_id' => $meeting->id,
        'membership_id' => $membership->id,
    ]);

    $this->actingAs($president)
        ->patch(route('groups.sessions.penalties.waive', [$group, $session, $penalty]), [
            'reason' => 'Justificatif accepté',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($penalty->refresh())
        ->status->toBe(PenaltyStatus::Waived)
        ->waived_by->toBe($president->id)
        ->waiver_reason->toBe('Justificatif accepté')
        ->waived_at->not->toBeNull();
});
