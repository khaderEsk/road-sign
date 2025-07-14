<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Product;
use App\Models\Region;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoadSign>
 */
class RoadSignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => Template::inRandomOrder()->value('id'),
            'city_id' => City::factory(),
            'region_id' => Region::factory(),
            'place' => $this->faker->address(),
            'is_available' => $this->faker->boolean(),
            'faces_number' => $this->faker->numberBetween(1, 4),
            'number' => $this->faker->unique()->numberBetween(1000, 9999),
            'directions' => $this->faker->randomElement(['N', 'S', 'E', 'W']),
            'advertising_meters' => $this->faker->randomFloat(2, 5, 50),
            'printing_meters' => $this->faker->randomFloat(2, 5, 50),
        ];
    }
}
