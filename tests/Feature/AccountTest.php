<?php

use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows only the authenticated users active tontines', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownTontine = Tontine::factory()->create();
    $otherTontine = Tontine::factory()->create();
    Membership::factory()->active()->for($user)->for($ownTontine)->create();
    Membership::factory()->active()->for($otherUser)->for($otherTontine)->create();

    $this->actingAs($user)
        ->get(route('account.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/index')
            ->where('summary.tontines_count', 1)
            ->has('collection.data', 1)
            ->where('collection.data.0.tontine.id', $ownTontine->id));
});

it('isolates personal insurance payments and rejects an inaccessible tontine filter', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tontine = Tontine::factory()->create(['currency' => 'XAF']);
    $membership = Membership::factory()->active()->for($user)->for($tontine)->create();
    $otherMembership = Membership::factory()->active()->for($otherUser)->for($tontine)->create();
    $session = Session::factory()->for($tontine)->create();
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
    $inaccessibleTontine = Tontine::factory()->create();

    $this->actingAs($user)
        ->get(route('account.insurance.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/insurance')
            ->has('collection.data', 1)
            ->where('collection.data.0.amount', '5000.00')
            ->where('totals.0.amount', '5000.00'));

    $this->actingAs($user)
        ->get(route('account.insurance.index', $inaccessibleTontine))
        ->assertNotFound();
});
