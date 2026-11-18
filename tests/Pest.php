<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Integration tests talk to a real MySQL database and manage their own schema.
pest()->extend(TestCase::class)
    ->in('Integration');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Point the test at a real MySQL database (row locks can't be tested on SQLite),
 * with a second connection to play "another staff member". Skips the test when
 * MySQL isn't running. Override the target with MYSQL_TEST_* env variables.
 */
function useMysqlLockingDatabase(TestCase $test): void
{
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
        $test->markTestSkipped('MySQL test database is not reachable; start MySQL in XAMPP to run the locking tests.');
    }

    config()->set('database.default', 'mysql_lock_test');
    DB::purge('mysql_lock_test');
    Artisan::call('migrate:fresh', ['--database' => 'mysql_lock_test', '--force' => true]);
}

/**
 * Roll back anything the "other staff member" connection left open.
 */
function closeMysqlLockingConnections(): void
{
    DB::connection('mysql_lock_test_other')->rollBack();
    DB::purge('mysql_lock_test_other');
    DB::purge('mysql_lock_test');
}
