<?php

use App\Models\User;
use Tests\TestCase;

test('guests are redirected to the login page', function () {
    /** @var TestCase $this */
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user);

    /** @var TestCase $this */
    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
