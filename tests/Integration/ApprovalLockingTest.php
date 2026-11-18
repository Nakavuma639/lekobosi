<?php

use App\Actions\Bookings\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Exceptions\BookingDateFullException;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/*
 * Row locking can only be proven on MySQL/MariaDB (SQLite has no SELECT ... FOR UPDATE).
 * These tests run against the database named in MYSQL_TEST_DATABASE
 * (default lehobothweb_db_testing) and skip when it isn't reachable.
 */

beforeEach(function () {
    $connection = [
        'driver' => 'mysql',
        'host' => env('MYSQL_TEST_HOST', '127.0.0.1'),
        'port' => env('MYSQL_TEST_PORT', '3306'),
        'database' => env('MYSQL_TEST_DATABASE', 'lehobothweb_db_testing'),
        'username' => env('MYSQL_TEST_USERNAME', 'root'),
        'password' => env('MYSQL_TEST_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    config()->set('database.connections.mysql_lock_test', $connection);
    config()->set('database.connections.mysql_lock_test_other', $connection);

    try {
        DB::connection('mysql_lock_test')->getPdo();
    } catch (Throwable) {
        $this->markTestSkipped('MySQL test database is not reachable; start MySQL in XAMPP to run the locking tests.');
    }

    config()->set('database.default', 'mysql_lock_test');
    DB::purge('mysql_lock_test');
    Artisan::call('migrate:fresh', ['--database' => 'mysql_lock_test', '--force' => true]);

    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create();
});

afterEach(function () {
    DB::connection('mysql_lock_test_other')->rollBack();
    DB::purge('mysql_lock_test_other');
    DB::purge('mysql_lock_test');
});

it('waits for the date lock held by another approval instead of reading stale data', function () {
    $first = Booking::factory()->on('2026-12-12')->create();
    $second = Booking::factory()->on('2026-12-12')->create();

    // Another admin's approval is in progress and holds the lock on that date.
    $other = DB::connection('mysql_lock_test_other');
    $other->beginTransaction();
    $other->table('bookings')->where('event_date', '2026-12-12')->lockForUpdate()->get();

    DB::connection('mysql_lock_test')->statement('SET SESSION innodb_lock_wait_timeout = 1');

    expect(fn () => app(ChangeBookingStatus::class)->handle($second, BookingStatus::Approved, $this->admin))
        ->toThrow(QueryException::class, 'Lock wait timeout');

    $other->rollBack();

    expect($second->refresh()->status)->toBe(BookingStatus::Pending)
        ->and($first->refresh()->status)->toBe(BookingStatus::Pending);
});

it('refuses the second approval once the first has committed', function () {
    $first = Booking::factory()->on('2026-12-12')->create();
    $second = Booking::factory()->on('2026-12-12')->create();

    app(ChangeBookingStatus::class)->handle($first, BookingStatus::Approved, $this->admin);

    expect(fn () => app(ChangeBookingStatus::class)->handle($second, BookingStatus::Approved, $this->admin))
        ->toThrow(BookingDateFullException::class);

    expect(Booking::query()->where('event_date', '2026-12-12')->where('status', BookingStatus::Approved)->count())->toBe(1);
});

it('switches to READ COMMITTED before locking the date', function () {
    $booking = Booking::factory()->on('2026-12-12')->create();
    $connection = DB::connection('mysql_lock_test');
    $connection->enableQueryLog();

    app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Approved, $this->admin);

    $queries = array_column($connection->getQueryLog(), 'query');
    $isolationIndex = array_search('SET TRANSACTION ISOLATION LEVEL READ COMMITTED', $queries, true);
    $lockIndex = collect($queries)->search(fn (string $query) => str_ends_with($query, 'for update'));

    expect($isolationIndex)->not->toBeFalse()
        ->and($lockIndex)->not->toBeFalse()
        ->and($isolationIndex)->toBeLessThan($lockIndex);
});

it('does not block approvals on other dates', function () {
    $other = DB::connection('mysql_lock_test_other');
    Booking::factory()->on('2026-12-12')->create();
    $differentDate = Booking::factory()->on('2026-12-19')->create();

    // The other approval runs the same way the app does: READ COMMITTED.
    $other->statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
    $other->beginTransaction();
    $other->table('bookings')->where('event_date', '2026-12-12')->lockForUpdate()->get();

    DB::connection('mysql_lock_test')->statement('SET SESSION innodb_lock_wait_timeout = 1');

    app(ChangeBookingStatus::class)->handle($differentDate, BookingStatus::Approved, $this->admin);

    expect($differentDate->refresh()->status)->toBe(BookingStatus::Approved);
});
