<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Terrain>
 */
class TerrainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => \App\Models\User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->address,
            'area_size' => $this->faker->randomFloat(2, 100, 10000), // 100 to 10,000 sq meters
            'price_per_day' => $this->faker->randomFloat(2, 50, 500), // $50 to $500 per day
            'available_from' => $this->faker->dateTimeBetween('now', '+1 month'),
            'available_to' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'is_available' => $this->faker->boolean(80), // 80% chance of being available
            'main_image' => $this->faker->imageUrl(800, 600, 'nature', true, 'terrain'),
        ];
    }
}
