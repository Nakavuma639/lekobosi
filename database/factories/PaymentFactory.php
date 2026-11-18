<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state. Fake data only.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->approved()->state(['quoted_amount' => 5_000_000]),
            'receipt_number' => fn () => 'RCT-'.now()->year.'-'.fake()->unique()->numerify('9####'),
            'amount' => 500_000,
            'method' => PaymentMethod::MtnMomo,
            'reference' => fake()->bothify('MP######.####.?#####'),
            'received_at' => now(),
            'recorded_by' => User::factory()->staff(),
            'note' => null,
            'status' => PaymentStatus::Confirmed,
        ];
    }

    /**
     * A payment that was voided by the owner.
     */
    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Voided,
            'voided_by' => User::factory()->admin(),
            'voided_at' => now(),
            'void_reason' => 'Entered twice by mistake.',
        ]);
    }
}
