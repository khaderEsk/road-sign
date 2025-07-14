<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 100, 10000);
        $paid = $this->faker->randomFloat(2, 0, $total);
        $remaining = $total - $paid;

        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'total' => $total,
            'paid' => $paid,
            'remaining' => $remaining,
            'payment_number'=>$this->faker->random_int(1000,9999),
            'date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }
}
