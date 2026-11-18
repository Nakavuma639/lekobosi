<?php

use App\Enums\UserRole;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '0772123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration stores a normalised phone number and a lowercase email', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'Test@Example.com',
        'phone' => '0772 123 456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->sole();

    expect($user->email)->toBe('test@example.com')
        ->and($user->phone)->toBe('+256772123456');
});

test('registration always creates a customer even if a role is posted', function () {
    $this->post('/register', [
        'name' => 'Sneaky User',
        'email' => 'sneaky@example.com',
        'phone' => '0772123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
        'is_active' => false,
    ]);

    $user = User::query()->sole();

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->is_active)->toBeTrue();
});

test('registration rejects an email that is already registered', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'phone' => '0772123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['email' => 'An account with this email already exists. Try logging in instead.']);

    $this->assertGuest();
});

test('registration rejects a phone number that is not Ugandan', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '12345',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['phone' => 'Enter a valid Ugandan phone number, e.g. 0772 123456.']);

    $this->assertGuest();
});

test('registration requires the password confirmation to match', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '0772123456',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('registration stores a hashed password', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '0772123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::query()->sole()->password)->not->toBe('password');
});
