<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            // CustomerSeeder::class,
            // BrokerSeeder::class,
            // CitySeeder::class,
            // RegionSeeder::class,
            // TemplateSeeder::class,
            // RoadSignSeeder::class,
            // ContractSeeder::class,
        ]);

    }
}
