<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\GroupPermission;
use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\User;
use App\Support\GroupPermissionChecker;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates a protected administrator role with every group permission', function (): void {
    [, $group] = roleManagementContext();
    $administrator = $group->roles()
        ->where('name', GroupRole::Administrator->value)
        ->sole();

    expect($administrator->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(
            array_map(
                fn (GroupPermission $permission): string => $permission->value,
                GroupPermission::cases(),
            ),
        );
});

it('removes stale group roles before deciding a permission', function (): void {
    app(PermissionSeeder::class)->run();

    $owner = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('groups.store'), [
            'name' => 'Groupe sécurisé',
            'member_number_prefix' => 'SEC',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'initial_mandate_name' => 'Mandat initial',
            'initial_mandate_starts_at' => today()->subDay()->toDateString(),
            'initial_mandate_ends_at' => today()->addYear()->toDateString(),
        ])
        ->assertSessionHasNoErrors();

    $group = Group::query()->where('user_id', $owner->id)->sole();
    app(CreateMembershipAction::class)->execute($group, $member, GroupRole::Member->value);

    setPermissionsTeamId($group->id);
    $member->syncRoles([GroupRole::Administrator->value]);
    $member->unsetRelation('roles');
    $member->unsetRelation('permissions');

    expect($member->can(GroupPermission::UpdateGroup->value))->toBeTrue()
        ->and(app(GroupPermissionChecker::class)->allows(
            $member,
            $group,
            GroupPermission::UpdateGroup,
        ))->toBeFalse();

    setPermissionsTeamId($group->id);
    $member->unsetRelation('roles');

    expect($member->hasRole(GroupRole::Administrator->value))->toBeFalse()
        ->and($member->hasRole(GroupRole::Member->value))->toBeTrue();
});

function roleManagementContext(): array
{
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute($group, $president, 'president');

    return [$president, $group];
}

it('allows the president to view and create a group role', function (): void {
    [$president, $group] = roleManagementContext();

    $this->actingAs($president)
        ->get(route('groups.roles.index', $group))
        ->assertInertia(fn (Assert $page) => $page
            ->component('roles/index')
            ->where('auth.authorization.group_id', $group->id)
            ->where('auth.authorization.permissions', fn ($permissions): bool => $permissions->contains(
                GroupPermission::CreateRoles->value,
            ) && $permissions->contains(
                GroupPermission::UpdateRoles->value,
            ))
            ->has('permissions', count(GroupPermission::cases())));

    $this->actingAs($president)
        ->post(route('groups.roles.store', $group), [
            'name' => 'Responsable des prêts',
            'permissions' => [
                GroupPermission::ViewLoans->value,
                GroupPermission::CreateLoans->value,
            ],
        ])
        ->assertSessionHasNoErrors();

    $role = Role::query()
        ->where('group_id', $group->id)
        ->where('name', 'Responsable des prêts')
        ->sole();

    expect($role->permissions()->pluck('name')->all())->toEqualCanonicalizing([
        GroupPermission::ViewLoans->value,
        GroupPermission::CreateLoans->value,
    ]);
});

it('allows a designated role manager to grant only permissions they possess', function (): void {
    [$president, $group] = roleManagementContext();
    $manager = User::factory()->create();
    app(CreateMembershipAction::class)->execute($group, $manager, 'member');

    setPermissionsTeamId($group->id);
    $managerRole = Role::query()->create([
        'name' => 'Gestionnaire des rôles',
        'guard_name' => 'web',
        'group_id' => $group->id,
    ]);
    $managerRole->syncPermissions([
        GroupPermission::ViewRoles->value,
        GroupPermission::CreateRoles->value,
        GroupPermission::ViewLoans->value,
    ]);
    $manager->syncRoles([$managerRole]);

    $this->actingAs($manager)
        ->post(route('groups.roles.store', $group), [
            'name' => 'Lecture des prêts',
            'permissions' => [GroupPermission::ViewLoans->value],
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($manager)
        ->post(route('groups.roles.store', $group), [
            'name' => 'Gestion financière',
            'permissions' => [GroupPermission::ManageInsurance->value],
        ])
        ->assertSessionHasErrors('permissions.0');
});

it('updates an existing role but protects the president role', function (): void {
    [$president, $group] = roleManagementContext();
    $secretary = $group->roles()->where('name', 'secretary')->sole();

    $this->actingAs($president)
        ->put(route('groups.roles.update', [$group, $secretary]), [
            'name' => 'Secrétaire principal',
            'permissions' => [
                GroupPermission::ViewGroup->value,
                GroupPermission::ViewMeetings->value,
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($secretary->refresh()->name)->toBe('Secrétaire principal')
        ->and($secretary->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            GroupPermission::ViewGroup->value,
            GroupPermission::ViewMeetings->value,
        ]);

    $presidentRole = $group->roles()->where('name', 'president')->sole();
    $this->actingAs($president)
        ->put(route('groups.roles.update', [$group, $presidentRole]), [
            'name' => 'Administrateur',
            'permissions' => [],
        ])
        ->assertForbidden();

    $administratorRole = $group->roles()->where('name', GroupRole::Administrator->value)->sole();
    $this->actingAs($president)
        ->put(route('groups.roles.update', [$group, $administratorRole]), [
            'name' => 'Administrateur modifié',
            'permissions' => [],
        ])
        ->assertForbidden();
});
