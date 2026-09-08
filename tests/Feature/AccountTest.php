<?php

use App\Actions\Groups\CreateDefaultGroupRolesAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Models\Group;
use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows only the authenticated users active groups', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownGroup = Group::factory()->create();
    $otherGroup = Group::factory()->create();
    Membership::factory()->active()->for($user)->for($ownGroup)->create();
    Membership::factory()->active()->for($otherUser)->for($otherGroup)->create();

    $this->actingAs($user)
        ->get(route('account.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/index')
            ->where('summary.groups_count', 1)
            ->has('collection.data', 1)
            ->where('collection.data.0.group.id', $ownGroup->id));
});

it('isolates personal insurance payments and rejects an inaccessible group filter', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $group = Group::factory()->create(['currency' => 'XAF']);
    $membership = Membership::factory()->active()->for($user)->for($group)->create();
    $otherMembership = Membership::factory()->active()->for($otherUser)->for($group)->create();
    $session = Session::factory()->for($group)->create();
    foreach ([$membership, $otherMembership] as $participantMembership) {
        SessionParticipant::query()->forceCreate([
            'session_id' => $session->id,
            'membership_id' => $participantMembership->id,
            'contribution_amount' => 50_000,
            'draw_entries_count' => 1,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }
    InsuranceContribution::factory()->for($session)->for($membership)->create(['amount' => '5000.00']);
    InsuranceContribution::factory()->for($session)->for($otherMembership)->create(['amount' => '9000.00']);
    $inaccessibleGroup = Group::factory()->create();

    $this->actingAs($user)
        ->get(route('account.insurance.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/insurance')
            ->has('collection.data', 1)
            ->where('collection.data.0.amount', '5000.00')
            ->where('totals.0.amount', '5000.00'));

    $this->actingAs($user)
        ->get(route('account.insurance.index', $inaccessibleGroup))
        ->assertNotFound();
});

it('allows a member to leave a group from their account space', function (): void {
    $user = User::factory()->create();
    $membership = Membership::factory()->active()->for($user)->for(Group::factory())->create();

    $this->actingAs($user)
        ->delete(route('account.memberships.destroy', $membership))
        ->assertRedirect(route('account.index'))
        ->assertSessionHasNoErrors();

    $leftMembership = Membership::withTrashed()->findOrFail($membership->id);

    expect($leftMembership->status->value)->toBe('left')
        ->and($leftMembership->left_at)->not->toBeNull()
        ->and($leftMembership->trashed())->toBeTrue();
});

it('does not allow a member to leave another users membership', function (): void {
    $user = User::factory()->create();
    $membership = Membership::factory()->active()->for(User::factory())->for(Group::factory())->create();

    $this->actingAs($user)
        ->delete(route('account.memberships.destroy', $membership))
        ->assertNotFound();

    expect($membership->fresh()->trashed())->toBeFalse();
});

it('does not allow the president in function to leave before transferring the presidency', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultGroupRolesAction::class)->execute($group);
    $membership = app(CreateMembershipAction::class)->execute($group, $president, 'president');

    $this->actingAs($president)
        ->delete(route('account.memberships.destroy', $membership))
        ->assertSessionHasErrors('membership');

    expect($membership->fresh()->isActive())->toBeTrue();
});
