<?php

use App\Models\ContactMessage;

/**
 * A valid contact form submission.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function contactForm(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ruth Nankya',
        'email' => 'Ruth@Example.com',
        'phone' => '0772 123 456',
        'subject' => 'Menu for 300 guests',
        'message' => 'Do you cater for introduction ceremonies in Hoima?',
    ], $overrides);
}

it('saves the message and confirms it was sent', function () {
    $this->post(route('contact.store'), contactForm())
        ->assertRedirect(route('contact.create'))
        ->assertSessionHas('contact_sent');

    $message = ContactMessage::query()->sole();

    expect($message->name)->toBe('Ruth Nankya')
        ->and($message->email)->toBe('ruth@example.com')
        ->and($message->phone)->toBe('+256772123456')
        ->and($message->subject)->toBe('Menu for 300 guests')
        ->and($message->message)->toBe('Do you cater for introduction ceremonies in Hoima?')
        ->and($message->is_read)->toBeFalse();

    $this->get(route('contact.create'))
        ->assertSee('Thank you. Your message has been sent and we will get back to you soon.');
});

it('accepts a message without phone or subject', function () {
    $this->post(route('contact.store'), contactForm(['phone' => '', 'subject' => '']))
        ->assertSessionHasNoErrors();

    $message = ContactMessage::query()->sole();
    expect($message->phone)->toBeNull()
        ->and($message->subject)->toBeNull();
});

it('strips HTML from the message', function () {
    $this->post(route('contact.store'), contactForm(['message' => '<a href="http://spam.example">Click</a> here for a quote please']));

    expect(ContactMessage::query()->sole()->message)->toBe('Click here for a quote please');
});

it('reports every missing required field', function () {
    $this->post(route('contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactMessage::query()->count())->toBe(0);
});

it('rejects invalid input with a clear message', function (array $input, string $field, string $message) {
    $this->post(route('contact.store'), contactForm($input))
        ->assertSessionHasErrors([$field => $message]);

    expect(ContactMessage::query()->count())->toBe(0);
})->with([
    'short message' => [['message' => 'Hi'], 'message', 'Your message is a little short. Please tell us a bit more (at least 10 characters).'],
    'long message' => [['message' => str_repeat('a', 2001)], 'message', 'The message field must not be greater than 2000 characters.'],
    'long subject' => [['subject' => str_repeat('a', 151)], 'subject', 'The subject field must not be greater than 150 characters.'],
    'bad phone' => [['phone' => '12345'], 'phone', 'Enter a valid Ugandan phone number, e.g. 0772 123456.'],
    'bad email' => [['email' => 'nope'], 'email', 'The email field must be a valid email address.'],
]);

it('turns away submissions that fill in the honeypot', function () {
    $this->post(route('contact.store'), contactForm(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(ContactMessage::query()->count())->toBe(0);
});

it('throttles repeated messages from the same visitor', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), contactForm())->assertRedirect();
    }

    $this->post(route('contact.store'), contactForm())->assertTooManyRequests();
});

it('has its own limit separate from bookings', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), contactForm());
    }

    $this->get(route('availability', ['month' => '2026-12']))->assertOk();
    $this->post(route('bookings.store'), [])->assertSessionHasErrors('fullname');
});
