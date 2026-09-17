<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

it('shares accessible groups with the sidebar', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $accessibleGroup = Group::factory()->create(['name' => 'Association Espoir']);
    $inaccessibleGroup = Group::factory()->create(['name' => 'Association privée']);

    app(CreateDefaultGroupRolesAction::class)->execute($accessibleGroup);
    app(CreateMembershipAction::class)->execute(
        $accessibleGroup,
        $user,
        GroupRole::Member->value,
    );
    app(CreateDefaultGroupRolesAction::class)->execute($inaccessibleGroup);
    app(CreateMembershipAction::class)->execute(
        $inaccessibleGroup,
        $otherUser,
        GroupRole::Member->value,
    );

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('navigation.groups', 1)
            ->where('navigation.groups.0.name', 'Association Espoir')
            ->where('navigation.groups.0.slug', $accessibleGroup->slug)
            ->where('navigation.sessions', []));
});

it('shares searchable session options only for the current group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $otherGroup = Group::factory()->create();

    app(CreateDefaultGroupRolesAction::class)->execute($group);
    app(CreateMembershipAction::class)->execute($group, $user, GroupRole::Member->value);

    Session::factory()->for($group)->create(['name' => 'Session 2025']);
    Session::factory()->for($group)->create(['name' => 'Session 2026']);
    Session::factory()->for($otherGroup)->create(['name' => 'Session étrangère']);

    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertInertia(fn (Assert $page) => $page
            ->has('navigation.sessions', 2)
            ->where('navigation.sessions.0.name', 'Session 2025')
            ->where('navigation.sessions.1.name', 'Session 2026'));
});
