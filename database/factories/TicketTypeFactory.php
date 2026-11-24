<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(), 'name' => 'General admission', 'description' => fake()->sentence(),
            'price' => '100.00', 'capacity' => 100, 'sold' => 0, 'minimum_per_order' => 1,
            'maximum_per_order' => 10, 'is_active' => true,
        ];
    }
}
