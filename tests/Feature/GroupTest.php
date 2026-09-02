<?php

use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->seed(PermissionSeeder::class);
});

test('guests cannot access the groups page', function (): void {
    /** @var TestCase $this */
    $response = $this->get(route('groups.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the groups page', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('groups.index'));

    $response->assertOk();
});

test('authenticated users can view the group creation page', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('groups.create'));

    $response->assertOk();
});

test('authenticated users can create a group', function (): void {
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
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    expect($group)
        ->user_id->toBe($user->id)
        ->name->toBe('AJERM')
        ->member_number_prefix->toBe('AJERM')
        ->default_loan_interest_rate->toBe('10.00')
        ->default_loan_term_months->toBe(5)
        ->description->toBe('Association des jeunes.')
        ->is_active->toBeTrue()
        ->is_public->toBeFalse()
        ->is_verified->toBeFalse()
        ->and($group->slug)
        ->toStartWith('ajerm-')
        ->toMatch('/^ajerm-[A-Za-z0-9]+$/');

    /** @var TestCase $this */
    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'user_id' => $user->id,
        'name' => 'AJERM',
        'slug' => $group->slug,
        'member_number_prefix' => 'AJERM',
        'default_loan_interest_rate' => '10.00',
        'default_loan_term_months' => 5,
        'description' => 'Association des jeunes.',
        'is_active' => true,
        'is_public' => false,
        'is_verified' => false,
    ]);
});

test('loan rate and term are mandatory when creating a group', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'Sans paramètres de prêt',
            'member_number_prefix' => 'SPP',
        ])
        ->assertSessionHasErrors([
            'default_loan_interest_rate',
            'default_loan_term_months',
        ]);
});

test('generated group slugs are unique', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($firstUser)
        ->post(route('groups.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
        ])
        ->assertSessionHasNoErrors();

    /** @var TestCase $this */
    $this
        ->actingAs($secondUser)
        ->post(route('groups.store'), [
            'name' => 'AJERM Canada',
            'member_number_prefix' => 'AJERMC',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
        ])
        ->assertSessionHasNoErrors();

    $firstGroup = Group::query()
        ->where('user_id', $firstUser->id)
        ->firstOrFail();

    $secondGroup = Group::query()
        ->where('user_id', $secondUser->id)
        ->firstOrFail();

    expect($firstGroup->slug)
        ->toStartWith('ajerm-')
        ->not->toBe($secondGroup->slug)
        ->and($secondGroup->slug)
        ->toStartWith('ajerm-canada-');
});

test('updating a group does not modify its slug', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Description initiale.',
        ])
        ->assertSessionHasNoErrors();

    $group = Group::query()
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    $originalSlug = $group->slug;
    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->put(route('groups.update', $group), [
            'name' => 'Association AJERM Canada',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '12.50',
            'default_loan_term_months' => 4,
            'description' => 'Nouvelle description.',
        ]);

    $response->assertSessionHasNoErrors();

    expect($group->refresh())
        ->name->toBe('Association AJERM Canada')
        ->description->toBe('Nouvelle description.')
        ->default_loan_interest_rate->toBe('12.50')
        ->default_loan_term_months->toBe(4)
        ->slug->toBe($originalSlug);
});

test('loan settings are serialized on the group listing, details and edit form', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'Group des prêts',
            'member_number_prefix' => 'TDP',
            'default_loan_interest_rate' => '8.75',
            'default_loan_term_months' => 6,
        ])
        ->assertSessionHasNoErrors();

    $group = Group::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)
        ->get(route('groups.index'))
        ->assertInertia(fn ($page) => $page
            ->where('collection.data.0.default_loan_interest_rate', '8.75')
            ->where('collection.data.0.default_loan_term_months', 6));

    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page
            ->where('group.default_loan_interest_rate', '8.75')
            ->where('group.default_loan_term_months', 6)
            ->where('group.can.view', true)
            ->where('group.can.update', true)
            ->where('group.can.delete', true));

    $this->actingAs($user)
        ->get(route('groups.edit', $group))
        ->assertInertia(fn ($page) => $page
            ->where('group.default_loan_interest_rate', '8.75')
            ->where('group.default_loan_term_months', 6));
});

test('protected group fields cannot be mass assigned', function (): void {
    Model::preventSilentlyDiscardingAttributes();

    $user = User::factory()->create();

    expect(
        fn () => $user->groups()->create([
            'name' => 'AJERM',
            'slug' => 'slug-interdit',
            'member_number_prefix' => 'AJERM',
            'is_active' => false,
            'is_public' => true,
            'is_verified' => true,
        ]),
    )->toThrow(MassAssignmentException::class);
});

test('creating a group creates its default roles', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Association des jeunes.',
        ])
        ->assertSessionHasNoErrors();

    $group = Group::query()
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    foreach (GroupRole::cases() as $role) {
        /** @var TestCase $this */
        $this->assertDatabaseHas('roles', [
            'name' => $role->value,
            'guard_name' => 'web',
            'group_id' => $group->id,
        ]);
    }
});
