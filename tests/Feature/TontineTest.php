<?php

use App\Enums\TontineRole;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;


uses(RefreshDatabase::class);


test('guests cannot access the tontines page', function () {

    $response = $this->get(route('tontines.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the tontines page', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('tontines.index'));

    $response->assertOk();
});

test('authenticated users can view the tontine creation page', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get(route('tontines.create'));

    $response->assertOk();
});

test('authenticated users can create a tontine', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'slug' => 'ajerm',
            'member_number_prefix' => 'AJERM',
            'description' => 'Association des jeunes.',
        ]);

    $response->assertSessionHasNoErrors();

    /** @var TestCase $this */
    $this->assertDatabaseHas('tontines', [
        'user_id' => $user->id,
        'name' => 'AJERM',
        'slug' => 'ajerm',
        'member_number_prefix' => 'AJERM',
    ]);
});

test('protected tontine fields cannot be mass assigned', function () {
    Model::preventSilentlyDiscardingAttributes();

    $user = User::factory()->create();

    expect(fn() => $user->tontines()->create([
        'name' => 'AJERM',
        'slug' => 'ajerm',
        'member_number_prefix' => 'AJERM',
        'is_active' => false,
        'is_public' => true,
        'is_verified' => true,
    ]))->toThrow(MassAssignmentException::class);
});


test('create tontine creates its default roles', function () {
    $user = User::factory()->create();

    /** @var TestCase $this */
    $this->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'slug' => 'ajerm',
            'member_number_prefix' => 'AJERM',
            'description' => 'Association des jeunes.',
        ])->assertSessionHasNoErrors();

    $tontine = Tontine::where('slug', 'ajerm')->firstOrFail();

    foreach (TontineRole::cases() as $role) {
        /** @var TestCase $this */
        $this->assertDatabaseHas('roles', [
            'name' => $role->value,
            'guard_name' => 'web',
            'tontine_id' => $tontine->id,
        ]);
    }
});
