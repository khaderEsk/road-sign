<?php

namespace Database\Factories;

use App\BookingType;
use App\Models\Customer;
use App\Models\RoadSign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+'.rand(1, 10).' days');

        return [
            'customer_id' => Customer::factory(),
            'road_sign_id' => RoadSign::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(BookingType::cases()),
            'number' => $this->faker->numberBetween(1, 5),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
        ];

    }
}
