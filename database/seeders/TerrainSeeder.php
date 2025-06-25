<?php

namespace Database\Seeders;

use App\Models\Terrain;
use Illuminate\Database\Seeder;
use App\Models\User;

class TerrainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 sample terrains
        Terrain::factory()->count(20)->create();
    }
}
