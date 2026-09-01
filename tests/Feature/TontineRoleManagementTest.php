<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\TontinePermission;
use App\Models\Tontine;
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
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');

    return [$president, $tontine];
}

it('allows the president to view and create a tontine role', function (): void {
    [$president, $tontine] = roleManagementContext();

    $this->actingAs($president)
        ->get(route('tontines.roles.index', $tontine))
        ->assertInertia(fn (Assert $page) => $page
            ->component('roles/index')
            ->where('can.create', true)
            ->where('can.update', true)
            ->has('permissions', count(TontinePermission::cases())));

    $this->actingAs($president)
        ->post(route('tontines.roles.store', $tontine), [
            'name' => 'Responsable des prêts',
            'permissions' => [
                TontinePermission::ViewLoans->value,
                TontinePermission::CreateLoans->value,
            ],
        ])
        ->assertSessionHasNoErrors();

    $role = Role::query()
        ->where('tontine_id', $tontine->id)
        ->where('name', 'Responsable des prêts')
        ->sole();

    expect($role->permissions()->pluck('name')->all())->toEqualCanonicalizing([
        TontinePermission::ViewLoans->value,
        TontinePermission::CreateLoans->value,
    ]);
});

it('allows a designated role manager to grant only permissions they possess', function (): void {
    [$president, $tontine] = roleManagementContext();
    $manager = User::factory()->create();
    app(CreateMembershipAction::class)->execute($tontine, $manager, 'member');

    setPermissionsTeamId($tontine->id);
    $managerRole = Role::query()->create([
        'name' => 'Gestionnaire des rôles',
        'guard_name' => 'web',
        'tontine_id' => $tontine->id,
    ]);
    $managerRole->syncPermissions([
        TontinePermission::ViewRoles->value,
        TontinePermission::CreateRoles->value,
        TontinePermission::ViewLoans->value,
    ]);
    $manager->syncRoles([$managerRole]);

    $this->actingAs($manager)
        ->post(route('tontines.roles.store', $tontine), [
            'name' => 'Lecture des prêts',
            'permissions' => [TontinePermission::ViewLoans->value],
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($manager)
        ->post(route('tontines.roles.store', $tontine), [
            'name' => 'Gestion financière',
            'permissions' => [TontinePermission::ManageInsurance->value],
        ])
        ->assertSessionHasErrors('permissions.0');
});

it('updates an existing role but protects the president role', function (): void {
    [$president, $tontine] = roleManagementContext();
    $secretary = $tontine->roles()->where('name', 'secretary')->sole();

    $this->actingAs($president)
        ->put(route('tontines.roles.update', [$tontine, $secretary]), [
            'name' => 'Secrétaire principal',
            'permissions' => [
                TontinePermission::ViewTontine->value,
                TontinePermission::ViewMeetings->value,
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($secretary->refresh()->name)->toBe('Secrétaire principal')
        ->and($secretary->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing([
            TontinePermission::ViewTontine->value,
            TontinePermission::ViewMeetings->value,
        ]);

    $presidentRole = $tontine->roles()->where('name', 'president')->sole();
    $this->actingAs($president)
        ->put(route('tontines.roles.update', [$tontine, $presidentRole]), [
            'name' => 'Administrateur',
            'permissions' => [],
        ])
        ->assertForbidden();
});
