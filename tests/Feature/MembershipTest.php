<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\DeactivateMembershipAction;
use App\Actions\Memberships\ReactivateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\MembershipStatus;
use App\Enums\TontineRole;
use App\Models\Membership;
use App\Models\Tontine;
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

test('creating a tontine automatically creates the creator membership as president', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Association des jeunes.',
        ]);

    $response->assertSessionHasNoErrors();

    $tontine = Tontine::query()
        ->where('name', 'AJERM')
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

    /** @var TestCase $this */
    $this->assertDatabaseMissing('model_has_roles', [
        'model_id' => $member->id,
        'model_type' => $member->getMorphClass(),
        'tontine_id' => $tontine->id,
    ]);

    /** @var TestCase $this */
    $this->assertSoftDeleted('memberships', [
        'id' => $membership->id,
    ]);
});

test('a soft deleted membership can be reactivated when the member rejoins', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole(
        $tontine,
        TontineRole::Member->value,
    );

    $membership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $member,
        invitedBy: $owner,
        roleName: TontineRole::Member->value,
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
        roleName: TontineRole::Member->value,
    );

    expect($reactivatedMembership)
        ->id->toBe($originalId)
        ->member_number->toBe($originalMemberNumber)
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

    expect(
        $member->hasRole(TontineRole::Member->value)
    )->toBeTrue();
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

    /** @var TestCase $this */
    $this->assertSoftDeleted('memberships', [
        'id' => $firstMembership->id,
    ]);
});

test('a new membership cannot be created when a soft deleted membership already exists', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $tontine = Tontine::factory()->create([
        'user_id' => $owner->id,
    ]);

    createTeamRole(
        $tontine,
        TontineRole::Member->value,
    );

    $membership = app(CreateMembershipAction::class)->execute(
        tontine: $tontine,
        user: $member,
        invitedBy: $owner,
        roleName: TontineRole::Member->value,
    );

    app(DeactivateMembershipAction::class)
        ->execute($membership);

    expect(
        fn () => app(CreateMembershipAction::class)->execute(
            tontine: $tontine,
            user: $member,
            invitedBy: $owner,
            roleName: TontineRole::Member->value,
        )
    )->toThrow(ValidationException::class);

    expect(
        Membership::withTrashed()
            ->whereBelongsTo($tontine)
            ->whereBelongsTo($member)
            ->count()
    )->toBe(1);
});
