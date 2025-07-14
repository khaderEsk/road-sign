<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Template;
use App\ProductType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Template::factory()
            ->count(10)
            ->create()
            ->each(function ($template) {
                foreach (ProductType::cases() as $type) {
                    Product::factory()
                        ->type($type)
                        ->create([
                            'template_id' => $template->id,
                        ]);
                }
            });
    }
}
