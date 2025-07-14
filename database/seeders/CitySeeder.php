<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\City::insert([
            ['name' => 'Damascus', 'is_active' => true],
            ['name' => 'Aleppo', 'is_active' => true],
            ['name' => 'Homs', 'is_active' => false],
        ]);
    }
}
