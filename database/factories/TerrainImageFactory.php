<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TerrainImage>
 */
class TerrainImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'terrain_id' => \App\Models\Terrain::factory(),
            'image_path' => $this->faker->imageUrl(800, 600, 'nature', true, 'terrain'),
            'uploaded_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
