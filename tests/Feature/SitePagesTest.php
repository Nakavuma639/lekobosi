<?php

use App\Models\User;

it('renders each public page with the shared site stylesheet', function (string $routeName, string $heading) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee(asset('css/style.css'), escape: false)
        ->assertSee($heading);
})->with([
    'home' => ['home', 'Making Your Special'],
    'about' => ['about', 'About Lekobosi'],
    'services' => ['services', 'Our Catering Services'],
    'booking' => ['bookings.create', 'Book Our Catering Services'],
    'track booking' => ['bookings.track', 'Track Your Booking'],
    'contact' => ['contact.create', 'Contact Lekobosi'],
    'login' => ['login', 'Login to Your Account'],
    'register' => ['register', 'Create Your Customer Account'],
]);

it('renders the booking form with the field names the backend reads', function () {
    $this->get(route('bookings.create'))
        ->assertOk()
        ->assertSee('name="_token"', escape: false)
        ->assertSeeInOrder([
            'name="fullname"', 'name="email"', 'name="phone"', 'name="whatsapp"',
            'name="event_type"', 'name="date"', 'name="location"', 'name="guests"', 'name="details"',
        ], escape: false);
});

it('renders the contact form with a named sender field', function () {
    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSeeInOrder(['name="name"', 'name="email"', 'name="message"'], escape: false);
});

it('renders the register form with the phone and confirm password fields', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSeeInOrder(['name="name"', 'name="email"', 'name="phone"', 'name="password"', 'name="confirm_password"'], escape: false);
});

it('shows login and register links to guests', function () {
    $this->get(route('home'))
        ->assertSee(route('login'))
        ->assertSee(route('register'))
        ->assertDontSee('>Logout<', escape: false);
});

it('shows a logout form instead of login links to signed-in users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertSee('>Logout<', escape: false)
        ->assertDontSee('>Login<', escape: false);
});
