<?php

namespace Database\Factories;

use App\ContractStatus;
use App\ContractType;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 months', '+1 months');
        $type = $this->faker->randomElement(['temporary', 'permanent']);
        $endDate = $type === 'temporary'
            ? (clone $startDate)->modify('+2 days')
            : (clone $startDate)->modify('+'.rand(5, 30).' days');

        return [
            'name' => 'Contract ' . $this->faker->unique()->word(),
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'broker_id' => Broker::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'type' =>  $this->faker->randomElement(ContractType::cases()),
            'status' => $this->faker->randomElement(ContractStatus::cases()),
        ];
    }
}
