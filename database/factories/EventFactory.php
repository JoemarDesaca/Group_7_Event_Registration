<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 week', '+3 months');

        return [
            'organizer_id' => User::factory(), 'title' => fake()->sentence(3), 'description' => fake()->paragraph(),
            'venue' => fake()->city(), 'starts_at' => $startsAt, 'ends_at' => (clone $startsAt)->modify('+3 hours'),
            'registration_opens_at' => now()->subDay(), 'registration_closes_at' => (clone $startsAt)->modify('-1 day'),
            'currency' => 'PHP', 'status' => 'published',
        ];
    }
}
