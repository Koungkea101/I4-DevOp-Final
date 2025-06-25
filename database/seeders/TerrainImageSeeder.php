<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TerrainImage;
use App\Models\Terrain;

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
