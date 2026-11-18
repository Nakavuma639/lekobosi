<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * Define the model's default state. Fake data only.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+2567'.fake()->numerify('########'),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'is_read' => false,
            'read_at' => null,
            'read_by' => null,
        ];
    }

    /**
     * A message the owner has already read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
            'read_by' => User::factory()->admin(),
        ]);
    }
}
