<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\DeactivateMembershipAction;
use App\Actions\Memberships\ReactivateMembershipAction;
use App\Enums\GroupRole;
use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    setPermissionsTeamId(null);
    /** @var TestCase $this */
    $this->seed(PermissionSeeder::class);
});

afterEach(function (): void {
    setPermissionsTeamId(null);
});

test('creating a group automatically creates the creator membership as president', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Association des jeunes.',
        ]);

    $response->assertSessionHasNoErrors();

    $group = Group::query()
        ->where('name', 'AJERM')
        ->firstOrFail();

    $membership = Membership::query()
        ->whereBelongsTo($group)
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
        ->where('group_id', $group->id)
        ->firstOrFail();

    $this->assertDatabaseHas('model_has_roles', [
        'role_id' => $role->id,
        'model_id' => $user->id,
        'model_type' => $user->getMorphClass(),
        'group_id' => $group->id,
    ]);
});

test('the same user cannot have two memberships in the same group', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $creator = User::factory()->create();

    createTeamRole($group, 'member');

    $action = app(CreateMembershipAction::class);

    $action->execute(
        group: $group,
        user: $user,
        invitedBy: $creator,
        roleName: 'member',
    );

    expect(
        fn () => $action->execute(
            group: $group,
            user: $user,
            invitedBy: $creator,
            roleName: 'member',
        )
    )->toThrow(ValidationException::class);

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($group)
            ->whereBelongsTo($user)
            ->count()
    )->toBe(1);
});

test('the last president cannot be deactivated', function () {
    $owner = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole($group, 'president');

    $membership = app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $owner,
        invitedBy: $owner,
        roleName: 'president',
    );

    expect(
        fn () => app(DeactivateMembershipAction::class)
            ->execute($membership)
    )->toThrow(ValidationException::class);

    /** @var TestCase $this */
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

    $group = Group::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole($group, 'member');

    $membership = app(CreateMembershipAction::class)->execute(
        group: $group,
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

    setPermissionsTeamId($group->id);

    $member->unsetRelation('roles');
    $member->unsetRelation('permissions');

    expect($member->hasRole('member'))->toBeFalse();

    /** @var TestCase $this */
    $this->assertDatabaseMissing('model_has_roles', [
        'model_id' => $member->id,
        'model_type' => $member->getMorphClass(),
        'group_id' => $group->id,
    ]);

    /** @var TestCase $this */
    $this->assertSoftDeleted('memberships', [
        'id' => $membership->id,
    ]);
});

test('a soft deleted membership can be reactivated when the member rejoins', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole(
        $group,
        GroupRole::Member->value,
    );

    $membership = app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $member,
        invitedBy: $owner,
        roleName: GroupRole::Member->value,
    );

    $originalId = $membership->id;
    $originalMemberNumber = $membership->member_number;

    app(DeactivateMembershipAction::class)
        ->execute($membership);

    $deletedMembership = Membership::withTrashed()
        ->whereKey($originalId)
        ->firstOrFail();

    expect($deletedMembership)
        ->status->toBe(MembershipStatus::Inactive)
        ->left_at->not->toBeNull()
        ->deleted_at->not->toBeNull();

    $reactivatedMembership = app(
        ReactivateMembershipAction::class
    )->execute(
        membership: $deletedMembership,
        roleName: GroupRole::Member->value,
    );

    expect($reactivatedMembership)
        ->id->toBe($originalId)
        ->member_number->toBe($originalMemberNumber)
        ->status->toBe(MembershipStatus::Active)
        ->left_at->toBeNull()
        ->deleted_at->toBeNull();

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($group)
            ->whereBelongsTo($member)
            ->count()
    )->toBe(1);

    setPermissionsTeamId($group->id);

    $member->unsetRelation('roles');
    $member->unsetRelation('permissions');

    expect(
        $member->hasRole(GroupRole::Member->value)
    )->toBeTrue();
});

function createTeamRole(
    Group $group,
    string $name,
): Role {
    $previousTeamId = getPermissionsTeamId();

    try {
        setPermissionsTeamId($group->id);

        return Role::findOrCreate($name, 'web');
    } finally {
        setPermissionsTeamId($previousTeamId);
    }
}

test('a president can be deactivated when another active president exists', function () {
    $owner = User::factory()->create();
    $secondPresident = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $owner->id,
    ]);

    app(CreateDefaultGroupRolesAction::class)
        ->execute($group);

    $firstMembership = app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $owner,
        roleName: GroupRole::President->value,
    );

    app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $secondPresident,
        invitedBy: $owner,
        roleName: GroupRole::President->value,
    );

    app(DeactivateMembershipAction::class)
        ->execute($firstMembership);

    /** @var TestCase $this */
    $this->assertSoftDeleted('memberships', [
        'id' => $firstMembership->id,
    ]);
});

test('a new membership cannot be created when a soft deleted membership already exists', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole(
        $group,
        GroupRole::Member->value,
    );

    $membership = app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $member,
        invitedBy: $owner,
        roleName: GroupRole::Member->value,
    );

    app(DeactivateMembershipAction::class)
        ->execute($membership);

    expect(
        fn () => app(CreateMembershipAction::class)->execute(
            group: $group,
            user: $member,
            invitedBy: $owner,
            roleName: GroupRole::Member->value,
        )
    )->toThrow(ValidationException::class);

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($group)
            ->whereBelongsTo($member)
            ->count()
    )->toBe(1);
});

test('a membership can be updated through its numeric scoped route', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'association-test',
    ]);

    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $owner,
        roleName: GroupRole::President->value,
    );
    $membership = app(CreateMembershipAction::class)->execute(
        group: $group,
        user: $member,
        invitedBy: $owner,
        roleName: GroupRole::Member->value,
    );

    $this->actingAs($owner)
        ->put(route('groups.memberships.update', [$group, $membership->id]), [
            'user_id' => $member->id,
            'role' => GroupRole::Secretary->value,
            'status' => MembershipStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    setPermissionsTeamId($group->id);
    $member->unsetRelation('roles');

    expect($membership->fresh()->status)->toBe(MembershipStatus::Active)
        ->and($member->hasRole(GroupRole::Secretary->value))->toBeTrue();
});
