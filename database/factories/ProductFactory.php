<?php

namespace Database\Factories;

use App\Models\Template;
use App\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => Template::factory(),
            'price' => $this->faker->randomFloat(2, 100, 500),
            'type' => $this->faker->randomElement(ProductType::cases()),
        ];
    }
    public function type(ProductType $type)
{
    return $this->state(fn () => ['type' => $type->value]);
}
}
