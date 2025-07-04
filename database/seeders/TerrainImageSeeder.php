<?php

namespace Database\Seeders;

use App\Models\Terrain;
use App\Models\TerrainImage;
use Illuminate\Database\Seeder;

class TerrainImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 2-5 images for each terrain
        $terrains = Terrain::all();

        foreach ($terrains as $terrain) {
            TerrainImage::factory()
                ->count(rand(2, 5))
                ->create(['terrain_id' => $terrain->id]);
        }
    }
}
