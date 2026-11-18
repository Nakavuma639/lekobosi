<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\BookingConflictAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingConflictAttempt>
 */
class BookingConflictAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_phone' => '+2567'.fake()->numerify('########'),
            'customer_email' => fake()->safeEmail(),
            'event_type' => fake()->randomElement(EventType::cases()),
            'requested_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
        ];
    }
}
