<?php

namespace Database\Factories;

use App\Models\AddOn;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AddOn>
 */
class AddOnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(), 'name' => 'Meal package', 'description' => fake()->sentence(),
            'price' => '25.00', 'capacity' => 100, 'sold' => 0, 'is_active' => true,
        ];
    }
}
