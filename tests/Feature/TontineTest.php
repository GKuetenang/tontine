<?php

use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access the tontines page', function () {
    $response = $this->get(route('tontines.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the tontines page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('tontines.index'));

    $response->assertOk();
});

test('authenticated users can view the tontine creation page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('tontines.create'));

    $response->assertOk();
});

test('authenticated users can create a tontine', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('tontines.store'), [
            'name' => 'AJERM',
            'slug' => 'ajerm',
            'description' => 'Association des jeunes.'
        ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tontines', [
        'user_id' => $user->id,
        'name' => 'AJERM',
        'slug' => 'ajerm',
    ]);
});

test('protected tontine fields cannot be mass assigned', function () {
    Model::preventSilentlyDiscardingAttributes();

    $user = User::factory()->create();

    expect(fn() => $user->tontines()->create([
        'name' => 'AJERM',
        'slug' => 'ajerm',
        'is_active' => false,
        'is_public' => true,
        'is_verified' => true,
    ]))->toThrow(MassAssignmentException::class);
});
