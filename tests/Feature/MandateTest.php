<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Mandates\ActivateMandateAction;
use App\Actions\Mandates\EndMandateRoleAssignmentAction;
use App\Actions\Mandates\SaveMandateRoleAssignmentAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Group;
use App\Models\Mandate;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Validation\ValidationException;

function mandateContext(): array
{
    app(PermissionSeeder::class)->run();
    $creator = User::factory()->create();
    $successor = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $creator->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    $creatorMembership = app(CreateMembershipAction::class)->execute($group, $creator, GroupRole::President->value);
    $successorMembership = app(CreateMembershipAction::class)->execute($group, $successor, GroupRole::Member->value);

    return [$creator, $successor, $group, $creatorMembership, $successorMembership];
}

it('keeps mandates independent from sessions', function (): void {
    $migration = file_get_contents(database_path('migrations/2026_09_02_000002_create_mandates_table.php'));

    expect($migration)->not->toContain('session_id');
});

it('activates a mandate and projects its active responsibilities to spatie roles', function (): void {
    [$creator, $successor, $group, , $successorMembership] = mandateContext();
    $mandate = Mandate::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'starts_at' => today()->subMonth(),
        'ends_at' => today()->addYear(),
    ]);
    $presidentRole = $group->roles()->where('name', GroupRole::President->value)->sole();

    app(SaveMandateRoleAssignmentAction::class)->execute(
        $mandate,
        $successorMembership,
        $presidentRole,
        $creator,
        today()->subMonth()->toDateString(),
        today()->addYear()->toDateString(),
    );
    app(ActivateMandateAction::class)->execute($mandate);

    setPermissionsTeamId($group->id);
    $creator->unsetRelation('roles');
    $successor->unsetRelation('roles');

    expect($mandate->refresh()->status)->toBe(MandateStatus::Active)
        ->and($successor->hasRole(GroupRole::President->value))->toBeTrue()
        ->and($successor->hasRole(GroupRole::Member->value))->toBeTrue()
        ->and($creator->hasRole(GroupRole::President->value))->toBeFalse()
        ->and($creator->hasRole(GroupRole::Member->value))->toBeTrue();
});

it('closes the previous mandate when a successor mandate is activated', function (): void {
    [$creator, $successor, $group, $creatorMembership, $successorMembership] = mandateContext();
    $presidentRole = $group->roles()->where('name', GroupRole::President->value)->sole();
    $action = app(SaveMandateRoleAssignmentAction::class);

    $first = Mandate::factory()->create(['group_id' => $group->id, 'created_by' => $creator->id]);
    $action->execute($first, $creatorMembership, $presidentRole, $creator, $first->starts_at->toDateString(), $first->ends_at->toDateString());
    app(ActivateMandateAction::class)->execute($first);

    $second = Mandate::factory()->create(['group_id' => $group->id, 'created_by' => $creator->id]);
    $action->execute($second, $successorMembership, $presidentRole, $creator, $second->starts_at->toDateString(), $second->ends_at->toDateString());
    app(ActivateMandateAction::class)->execute($second);

    expect($first->refresh()->status)->toBe(MandateStatus::Closed)
        ->and($second->refresh()->status)->toBe(MandateStatus::Active);
});

it('rejects overlapping presidents in the same mandate', function (): void {
    [$creator, $successor, $group, $creatorMembership, $successorMembership] = mandateContext();
    $mandate = Mandate::factory()->create(['group_id' => $group->id, 'created_by' => $creator->id]);
    $role = $group->roles()->where('name', GroupRole::President->value)->sole();
    $action = app(SaveMandateRoleAssignmentAction::class);
    $action->execute($mandate, $creatorMembership, $role, $creator, $mandate->starts_at->toDateString(), $mandate->ends_at->toDateString());

    expect(fn () => $action->execute($mandate, $successorMembership, $role, $creator, $mandate->starts_at->toDateString(), $mandate->ends_at->toDateString()))
        ->toThrow(ValidationException::class);
});

it('keeps a trace when a responsibility ends during an active mandate', function (): void {
    [$creator, , $group, $creatorMembership] = mandateContext();
    $mandate = Mandate::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'starts_at' => today()->subMonth(),
        'ends_at' => today()->addYear(),
    ]);
    $role = $group->roles()->where('name', GroupRole::President->value)->sole();
    $assignment = app(SaveMandateRoleAssignmentAction::class)->execute(
        $mandate,
        $creatorMembership,
        $role,
        $creator,
        today()->subMonth()->toDateString(),
        today()->addYear()->toDateString(),
    );
    app(ActivateMandateAction::class)->execute($mandate);

    app(EndMandateRoleAssignmentAction::class)->execute(
        $assignment,
        $creator,
        today()->toDateString(),
        'Fin de fonction',
    );

    expect($assignment->refresh()->end_reason)->toBe('Fin de fonction')
        ->and($assignment->ended_by)->toBe($creator->id)
        ->and($assignment->exists)->toBeTrue();
});

it('rejects direct governance role assignment after mandates become authoritative', function (): void {
    [$creator, , $group, $creatorMembership] = mandateContext();
    $mandate = Mandate::factory()->create(['group_id' => $group->id, 'created_by' => $creator->id]);
    $role = $group->roles()->where('name', GroupRole::President->value)->sole();
    app(SaveMandateRoleAssignmentAction::class)->execute(
        $mandate,
        $creatorMembership,
        $role,
        $creator,
        $mandate->starts_at->toDateString(),
        $mandate->ends_at->toDateString(),
    );
    app(ActivateMandateAction::class)->execute($mandate);

    expect(fn () => app(CreateMembershipAction::class)->execute(
        $group,
        User::factory()->create(),
        GroupRole::Secretary->value,
    ))->toThrow(ValidationException::class);
});

it('searches only active group memberships for a mandate assignment', function (): void {
    [$creator, $member, $group, , $membership] = mandateContext();
    User::factory()->create(['first_name' => $member->first_name, 'name' => $member->name]);

    $response = $this->actingAs($creator)->get(
        route('groups.mandates.index', [
            'group' => $group,
            'q_search' => $member->first_name,
        ]),
        [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'mandates/index',
            'X-Inertia-Partial-Data' => 'users',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ],
    );

    $response->assertOk()->assertJsonPath('props.users.0.id', $membership->id);
    expect(collect($response->json('props.users'))->pluck('id')->all())->toBe([$membership->id]);
});

it('updates a member responsibility through the unscoped membership route binding', function (): void {
    [$creator, , $group, , $membership] = mandateContext();
    $mandate = Mandate::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
    ]);
    $secretaryRole = $group->roles()->where('name', GroupRole::Secretary->value)->sole();

    $this->actingAs($creator)
        ->put(route('groups.mandates.assignments.update', [$group, $mandate, $membership]), [
            'role_id' => $secretaryRole->id,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('mandate_role_assignments', [
        'mandate_id' => $mandate->id,
        'membership_id' => $membership->id,
        'role_id' => $secretaryRole->id,
    ]);
});

it('transfers an active presidency from the member management table while preserving history', function (): void {
    [$creator, $successor, $group, $creatorMembership, $successorMembership] = mandateContext();
    $mandate = Mandate::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'starts_at' => today()->subMonth(),
        'ends_at' => today()->addYear(),
    ]);
    $presidentRole = $group->roles()->where('name', GroupRole::President->value)->sole();
    $formerAssignment = app(SaveMandateRoleAssignmentAction::class)->execute(
        $mandate,
        $creatorMembership,
        $presidentRole,
        $creator,
        $mandate->starts_at->toDateString(),
        $mandate->ends_at->toDateString(),
    );
    app(ActivateMandateAction::class)->execute($mandate);

    $this->actingAs($creator)
        ->put(route('groups.mandates.assignments.update', [$group, $mandate, $successorMembership]), [
            'role_id' => $presidentRole->id,
        ])
        ->assertSessionHasNoErrors();

    setPermissionsTeamId($group->id);
    $creator->unsetRelation('roles');
    $successor->unsetRelation('roles');

    expect($formerAssignment->refresh()->ends_at->toDateString())->toBe(today()->subDay()->toDateString())
        ->and($formerAssignment->end_reason)->toBe('Changement de responsabilité')
        ->and($creator->hasRole(GroupRole::President->value))->toBeFalse()
        ->and($successor->hasRole(GroupRole::President->value))->toBeTrue();
});
