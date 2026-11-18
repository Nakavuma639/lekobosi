<?php

namespace Database\Factories;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Models\Booking;
use App\Models\User;
use App\Support\BookingReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state. Fake data only, never real customers.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone = '+2567'.fake()->numerify('########');

        return [
            'reference' => fn () => BookingReference::generate(),
            'user_id' => null,
            'created_by' => null,
            'source' => BookingSource::Online,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => $phone,
            'whatsapp_number' => $phone,
            'event_type' => fake()->randomElement(EventType::cases()),
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'event_location' => fake()->city(),
            'guest_count' => fake()->numberBetween(20, 600),
            'extra_requirements' => null,
            'status' => BookingStatus::Pending,
            'status_changed_at' => now(),
            'decided_by' => null,
            'decision_note' => null,
            'quoted_amount' => null,
        ];
    }

    /**
     * A booking the owner has approved, which blocks its date.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Approved,
            'decided_by' => User::factory()->admin(),
        ]);
    }

    /**
     * A booking the owner has rejected, with the reason.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Rejected,
            'decided_by' => User::factory()->admin(),
            'decision_note' => 'We are fully booked for that weekend.',
        ]);
    }

    /**
     * A booking for a specific date.
     */
    public function on(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'event_date' => $date,
        ]);
    }

    /**
     * A booking made from a customer account.
     */
    public function forCustomer(User $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
        ]);
    }
}
