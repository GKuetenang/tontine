<?php

use App\Enums\TontineRole;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('guests cannot access the tontines page', function (): void {
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
        'description' => 'Association des jeunes.',
        'is_active' => true,
        'is_public' => false,
        'is_verified' => false,
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
        ])
        ->assertSessionHasNoErrors();

    /** @var TestCase $this */
    $this
        ->actingAs($secondUser)
        ->post(route('tontines.store'), [
            'name' => 'AJERM Canada',
            'member_number_prefix' => 'AJERMC',
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
            'description' => 'Nouvelle description.',
        ]);

    $response->assertSessionHasNoErrors();

    expect($tontine->refresh())
        ->name->toBe('Association AJERM Canada')
        ->description->toBe('Nouvelle description.')
        ->slug->toBe($originalSlug);
});

test('protected tontine fields cannot be mass assigned', function (): void {
    Model::preventSilentlyDiscardingAttributes();

    $user = User::factory()->create();

    expect(
        fn() => $user->tontines()->create([
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
