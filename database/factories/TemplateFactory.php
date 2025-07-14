<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model' => $this->faker->word(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['نوع أ', 'نوع ب']),
            'size' => $this->faker->randomElement(['2x3', '3x4', '4x6']),
            'advertising_space' => $this->faker->randomFloat(2, 1, 100),
            'printing_space' => $this->faker->randomFloat(2, 1, 100),
            // 'advertising_meter_price' => $this->faker->randomFloat(2, 50, 200),
            // 'printing_meter_price' => $this->faker->randomFloat(2, 20, 100),
        ];
    }
}
