<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Broker;

class BrokerSeeder extends Seeder
{
    public function run(): void
    {
        Broker::factory()->count(100)->create();
    }
}
