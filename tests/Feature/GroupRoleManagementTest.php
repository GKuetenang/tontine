<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

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
            ->where('can.create', true)
            ->where('can.update', true)
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
});
