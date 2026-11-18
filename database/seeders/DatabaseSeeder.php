<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The real owner account is created with `php artisan lekobosi:create-admin`,
     * never by a seeder. `php artisan db:seed` loads fake demo data for local
     * testing only (see DemoDataSeeder; it refuses to run in production).
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
