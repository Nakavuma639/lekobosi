<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureGates();

        DevCommands::withoutVendorCommands();
        DevCommands::withoutDefaultCommands();

        DevCommands::artisan('serve', 'server');

        // Mail is queued, so without a worker running beside the server a
        // confirmation would sit in the jobs table and never be sent.
        DevCommands::artisan('queue:listen --tries=1 --timeout=0', 'queue');
        DevCommands::node('dev', 'vite');
    }

    /**
     * Abilities that aren't about a single model (spec §6.5).
     */
    private function configureGates(): void
    {
        Gate::define('view-reports', fn (User $user) => $user->isBackOffice());
        Gate::define('export-reports', fn (User $user) => $user->hasRole(UserRole::Admin));
    }

    /**
     * Named limiters give each public endpoint its own bucket. Plain
     * `throttle:N,M` keys only on the visitor, so every throttled route would
     * otherwise share one counter.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('bookings', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('availability', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('registrations', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Only lookups count, so opening the empty track page is never blocked.
        // Ten per minute makes guessing references impractical.
        RateLimiter::for('tracking', fn (Request $request) => $request->filled('reference')
            ? Limit::perMinute(10)->by($request->ip())
            : Limit::none());
    }
}
