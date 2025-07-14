<?php

namespace Database\Seeders;

use App\Models\RoadSign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoadSignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoadSign::factory()->count(10)->create();
    }
}
