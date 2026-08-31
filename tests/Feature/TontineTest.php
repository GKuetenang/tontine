<?php

use App\Enums\TontineRole;
use App\Models\Tontine;
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

test('guests cannot access the tontines page', function (): void {
    /** @var TestCase $this */
    $response = $this->get(route('tontines.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the tontines page', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('tontines.index'));

    $response->assertOk();
});

test('authenticated users can view the tontine creation page', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('tontines.create'));

    $response->assertOk();
});

test('authenticated users can create a tontine', function (): void {
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
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    expect($tontine)
        ->user_id->toBe($user->id)
        ->name->toBe('AJERM')
        ->member_number_prefix->toBe('AJERM')
        ->default_loan_interest_rate->toBe('10.00')
        ->default_loan_term_months->toBe(5)
        ->description->toBe('Association des jeunes.')
        ->is_active->toBeTrue()
        ->is_public->toBeFalse()
        ->is_verified->toBeFalse()
        ->and($tontine->slug)
        ->toStartWith('ajerm-')
        ->toMatch('/^ajerm-[A-Za-z0-9]+$/');

    /** @var TestCase $this */
    $this->assertDatabaseHas('tontines', [
        'id' => $tontine->id,
        'user_id' => $user->id,
        'name' => 'AJERM',
        'slug' => $tontine->slug,
        'member_number_prefix' => 'AJERM',
        'default_loan_interest_rate' => '10.00',
        'default_loan_term_months' => 5,
        'description' => 'Association des jeunes.',
        'is_active' => true,
        'is_public' => false,
        'is_verified' => false,
    ]);
});

test('loan rate and term are mandatory when creating a tontine', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'Sans paramètres de prêt',
            'member_number_prefix' => 'SPP',
        ])
        ->assertSessionHasErrors([
            'default_loan_interest_rate',
            'default_loan_term_months',
        ]);
});

test('generated tontine slugs are unique', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($firstUser)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
        ])
        ->assertSessionHasNoErrors();

    /** @var TestCase $this */
    $this
        ->actingAs($secondUser)
        ->post(route('tontines.store'), [
            'name' => 'AJERM Canada',
            'member_number_prefix' => 'AJERMC',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
        ])
        ->assertSessionHasNoErrors();

    $firstTontine = Tontine::query()
        ->where('user_id', $firstUser->id)
        ->firstOrFail();

    $secondTontine = Tontine::query()
        ->where('user_id', $secondUser->id)
        ->firstOrFail();

    expect($firstTontine->slug)
        ->toStartWith('ajerm-')
        ->not->toBe($secondTontine->slug)
        ->and($secondTontine->slug)
        ->toStartWith('ajerm-canada-');
});

test('updating a tontine does not modify its slug', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Description initiale.',
        ])
        ->assertSessionHasNoErrors();

    $tontine = Tontine::query()
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    $originalSlug = $tontine->slug;
    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->put(route('tontines.update', $tontine), [
            'name' => 'Association AJERM Canada',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '12.50',
            'default_loan_term_months' => 4,
            'description' => 'Nouvelle description.',
        ]);

    $response->assertSessionHasNoErrors();

    expect($tontine->refresh())
        ->name->toBe('Association AJERM Canada')
        ->description->toBe('Nouvelle description.')
        ->default_loan_interest_rate->toBe('12.50')
        ->default_loan_term_months->toBe(4)
        ->slug->toBe($originalSlug);
});

test('loan settings are serialized on the tontine listing, details and edit form', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'Tontine des prêts',
            'member_number_prefix' => 'TDP',
            'default_loan_interest_rate' => '8.75',
            'default_loan_term_months' => 6,
        ])
        ->assertSessionHasNoErrors();

    $tontine = Tontine::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)
        ->get(route('tontines.index'))
        ->assertInertia(fn ($page) => $page
            ->where('collection.data.0.default_loan_interest_rate', '8.75')
            ->where('collection.data.0.default_loan_term_months', 6));

    $this->actingAs($user)
        ->get(route('tontines.show', $tontine))
        ->assertInertia(fn ($page) => $page
            ->where('tontine.default_loan_interest_rate', '8.75')
            ->where('tontine.default_loan_term_months', 6));

    $this->actingAs($user)
        ->get(route('tontines.edit', $tontine))
        ->assertInertia(fn ($page) => $page
            ->where('tontine.default_loan_interest_rate', '8.75')
            ->where('tontine.default_loan_term_months', 6));
});

test('protected tontine fields cannot be mass assigned', function (): void {
    Model::preventSilentlyDiscardingAttributes();

    $user = User::factory()->create();

    expect(
        fn () => $user->tontines()->create([
            'name' => 'AJERM',
            'slug' => 'slug-interdit',
            'member_number_prefix' => 'AJERM',
            'is_active' => false,
            'is_public' => true,
            'is_verified' => true,
        ]),
    )->toThrow(MassAssignmentException::class);
});

test('creating a tontine creates its default roles', function (): void {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'member_number_prefix' => 'AJERM',
            'default_loan_interest_rate' => '10.00',
            'default_loan_term_months' => 5,
            'description' => 'Association des jeunes.',
        ])
        ->assertSessionHasNoErrors();

    $tontine = Tontine::query()
        ->where('user_id', $user->id)
        ->where('name', 'AJERM')
        ->firstOrFail();

    foreach (TontineRole::cases() as $role) {
        /** @var TestCase $this */
        $this->assertDatabaseHas('roles', [
            'name' => $role->value,
            'guard_name' => 'web',
            'tontine_id' => $tontine->id,
        ]);
    }
});
