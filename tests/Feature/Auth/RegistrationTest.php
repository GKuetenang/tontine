<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'name' => 'User',
        'email' => 'test@example.com',
        'username' => 'tes-username',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->user()->first_name)->toBe('Test')
        ->and(auth()->user()->name)->toBe('User')
        ->and(auth()->user()->full_name)->toBe('Test User');
});
