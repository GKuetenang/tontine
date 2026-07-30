<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\DeactivateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\MembershipStatus;
use App\Enums\TontineRole;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    setPermissionsTeamId(null);
});

afterEach(function (): void {
    setPermissionsTeamId(null);
});

test('creating a tontine automatically creates the creator membership as president', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'slug' => 'ajerm',
            'member_number_prefix' => 'AJERM',
            'description' => 'Association des jeunes.',
        ]);

    $response->assertSessionHasNoErrors();

    $tontine = Tontine::query()
        ->where('slug', 'ajerm')
        ->firstOrFail();

    $membership = Membership::query()
        ->whereBelongsTo($tontine)
        ->whereBelongsTo($user)
        ->firstOrFail();

    expect($membership)
        ->status->toBe(MembershipStatus::Active)
        ->member_number->toBe('AJERM-000001')
        ->joined_at->not->toBeNull()
        ->verified_at->not->toBeNull()
        ->left_at->toBeNull();

    $role = Role::query()
        ->where('name', 'president')
        ->where('guard_name', 'web')
        ->where('tontine_id', $tontine->id)
        ->firstOrFail();

    $this->assertDatabaseHas('model_has_roles', [
        'role_id' => $role->id,
        'model_id' => $user->id,
        'model_type' => $user->getMorphClass(),
        'tontine_id' => $tontine->id,
    ]);
});

test('the same user cannot have two memberships in the same tontine', function () {
    $tontine = Tontine::factory()->create();
    $user = User::factory()->create();
    $creator = User::factory()->create();

    createTeamRole($tontine, 'member');

    $action = app(CreateMembershipAction::class);

    $action->execute(
        tontine: $tontine,
        user: $user,
        invitedBy: $creator,
        roleName: 'member',
    );

    expect(
        fn () => $action->execute(
            tontine: $tontine,
            user: $user,
            invitedBy: $creator,
            roleName: 'member',
        )
    )->toThrow(ValidationException::class);

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($tontine)
            ->whereBelongsTo($user)
            ->count()
    )->toBe(1);
});

test('the last president cannot be deactivated', function () {
    $owner = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole($tontine, 'president');

    $membership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $owner,
        invitedBy: $owner,
        roleName: 'president',
    );

    expect(
        fn () => app(DeactivateMembershipAction::class)
            ->execute($membership)
    )->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('memberships', [
        'id' => $membership->id,
        'deleted_at' => now(),
    ]);

    expect($membership->fresh()->status)
        ->toBe(MembershipStatus::Active);
});

test('deactivating a membership removes its team roles and soft deletes it', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole($tontine, 'member');

    $membership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $member,
        invitedBy: $owner,
        roleName: 'member',
    );

    app(DeactivateMembershipAction::class)
        ->execute($membership);

    $deletedMembership = Membership::withTrashed()
        ->findOrFail($membership->id);
    expect($deletedMembership)
        ->status->toBe(MembershipStatus::Inactive)
        ->left_at->not->toBeNull()
        ->deleted_at->not->toBeNull();

    setPermissionsTeamId($tontine->id);

    $member->unsetRelation('roles');
    $member->unsetRelation('permissions');

    expect($member->hasRole('member'))->toBeFalse();

    $this->assertDatabaseMissing('model_has_roles', [
        'model_id' => $member->id,
        'model_type' => $member->getMorphClass(),
        'tontine_id' => $tontine->id,
    ]);

    $this->assertSoftDeleted('memberships', [
        'id' => $membership->id,
    ]);
});

test('a soft deleted membership is restored when the member rejoins', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole($tontine, 'member');

    $membership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $member,
        invitedBy: $owner,
        roleName: 'member',
    );

    app(DeactivateMembershipAction::class)
        ->execute($membership);

    $restoredMembership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $member,
        invitedBy: $owner,
        roleName: 'member',
    );

    expect($restoredMembership)
        ->id->toBe($membership->id)
        ->member_number->toBe($membership->member_number)
        ->status->toBe(MembershipStatus::Active)
        ->left_at->toBeNull()
        ->deleted_at->toBeNull();

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($tontine)
            ->whereBelongsTo($member)
            ->count()
    )->toBe(1);

    setPermissionsTeamId($tontine->id);

    $member->unsetRelation('roles');
    $member->unsetRelation('permissions');

    expect($member->hasRole('member'))->toBeTrue();
});

function createTeamRole(
    Tontine $tontine,
    string $name,
): Role {
    $previousTeamId = getPermissionsTeamId();

    try {
        setPermissionsTeamId($tontine->id);

        return Role::findOrCreate($name, 'web');
    } finally {
        setPermissionsTeamId($previousTeamId);
    }
}

test('a president can be deactivated when another active president exists', function () {
    $owner = User::factory()->create();
    $secondPresident = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    app(CreateDefaultTontineRolesAction::class)
        ->execute($tontine);

    $firstMembership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $owner,
        roleName: TontineRole::President->value,
    );

    app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $secondPresident,
        invitedBy: $owner,
        roleName: TontineRole::President->value,
    );

    app(DeactivateMembershipAction::class)
        ->execute($firstMembership);

    $this->assertSoftDeleted('memberships', [
        'id' => $firstMembership->id,
    ]);
});
