<?php

namespace Database\Factories;

use Ds\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'amt' => $this->faker->randomFloat(2, 0.1),
            'dcc_amount' => $this->faker->randomFloat(2, 0.1),
            'shipping_amt' => $this->faker->randomFloat(2, 0.1),
            'tax_amt' => $this->faker->randomFloat(2, 0.1),
            'transaction_id' => $this->faker->uuid,
            'order_time' => $this->faker->dateTime,
            'functional_total' => $this->faker->randomFloat(2, 0.1),
        ];
    }

    public function paid(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'Completed',
            ];
        });
    }
}
