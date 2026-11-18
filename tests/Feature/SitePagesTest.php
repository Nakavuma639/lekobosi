<?php

use App\Models\User;

it('renders each public page', function (string $routeName, string $heading) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee($heading);
})->with([
    'home' => ['home', 'Food for the days your family will talk about for years'],
    'about' => ['about', 'A family kitchen that grew into an events team'],
    'services' => ['services', 'What we cater'],
    'booking' => ['bookings.create', 'Request a booking'],
    'track booking' => ['bookings.track', 'Track your booking'],
    'contact' => ['contact.create', 'Get in touch'],
    'login' => ['login', 'Welcome back'],
    'register' => ['register', 'Create your account'],
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

it('offers the event types from the client frontend', function () {
    $this->get(route('bookings.create'))
        ->assertSeeInOrder(['Wedding', 'Introduction Ceremony', 'Birthday Party', 'Corporate Event', 'Conference', 'Other']);
});

it('renders the contact form with every message field', function () {
    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSeeInOrder(['name="name"', 'name="email"', 'name="phone"', 'name="subject"', 'name="message"'], escape: false);
});

it('renders the register form with Laravel confirmation field names', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSeeInOrder(['name="name"', 'name="email"', 'name="phone"', 'name="password"', 'name="password_confirmation"'], escape: false);
});

it('shows a log in link to guests', function () {
    $this->get(route('home'))
        ->assertSee(route('login'))
        ->assertDontSee('My dashboard');
});

it('shows a dashboard link and log out button to signed-in users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertSee('My dashboard')
        ->assertSee('Log out');
});
