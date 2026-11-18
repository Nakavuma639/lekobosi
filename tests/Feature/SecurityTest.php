<?php

use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

/**
 * Every route under /admin, with its parameters filled from real records.
 *
 * @return list<array{method: string, uri: string}>
 */
function adminRoutes(): array
{
    $booking = Booking::factory()->approved()->create(['quoted_amount' => 1_000_000]);
    $payment = Payment::factory()->create(['booking_id' => $booking->id]);
    $message = ContactMessage::factory()->create();
    $staff = User::factory()->staff()->create();

    $parameters = [
        '{booking}' => $booking->reference,
        '{payment}' => $payment->receipt_number,
        '{contactMessage}' => (string) $message->id,
        '{user}' => (string) $staff->id,
    ];

    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RoutingRoute $route) => str_starts_with($route->uri(), 'admin'))
        ->flatMap(fn (RoutingRoute $route) => collect($route->methods())
            ->reject(fn (string $method) => $method === 'HEAD')
            ->map(fn (string $method) => ['method' => $method, 'uri' => '/'.strtr($route->uri(), $parameters)]))
        ->values()
        ->all();
}

it('returns 403 to a customer on every back-office route', function () {
    $routes = adminRoutes();
    $customer = User::factory()->create();

    expect($routes)->not->toBeEmpty();

    foreach ($routes as $route) {
        $status = $this->actingAs($customer)->call($route['method'], $route['uri'])->getStatusCode();

        expect($status)->toBe(403, "{$route['method']} {$route['uri']} returned {$status} to a customer");
    }
});

it('sends guests to the login page from every back-office route', function () {
    foreach (adminRoutes() as $route) {
        $this->call($route['method'], $route['uri'])->assertRedirect(route('login'));
    }
});

it('stores passwords hashed, never as typed', function () {
    $user = User::factory()->create(['password' => 'a-secret-password']);

    expect($user->getRawOriginal('password'))->not->toBe('a-secret-password')
        ->and(password_get_info($user->getRawOriginal('password'))['algo'])->not->toBeNull();
});

it('adds security headers to every page', function () {
    $this->get(route('home'))
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('does not force HTTPS or send HSTS unless configured', function () {
    $this->get('http://localhost/')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});

it('redirects HTTP to HTTPS and sends HSTS when forced', function () {
    config()->set('lekobosi.security.force_https', true);

    $this->get('http://localhost/about')->assertRedirect('https://localhost/about')->assertStatus(301);

    $this->get('https://localhost/about')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('does not reveal error details when debug mode is off', function () {
    config()->set('app.debug', false);
    Route::get('/__boom', fn () => throw new RuntimeException('database password is hunter2'));

    $this->get('/__boom')
        ->assertStatus(500)
        ->assertDontSee('hunter2')
        ->assertDontSee('RuntimeException');
});

it('runs CSRF protection on every web route', function () {
    // Laravel skips the CSRF check itself while tests run, so assert the
    // middleware is in place instead of sending a token-less request.
    $webMiddleware = app(Kernel::class)->getMiddlewareGroups()['web'];

    expect($webMiddleware)->toContain(PreventRequestForgery::class);
});
