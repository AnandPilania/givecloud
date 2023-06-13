<?php

namespace Database\Factories;

use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringPaymentProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecurringPaymentProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => RecurringPaymentProfileStatus::ACTIVE,
            'subscriber_name' => $this->faker->name,
            'profile_reference' => $this->faker->isbn13,
            'description' => $this->faker->sentence(6),
            'aggregate_amount' => 0.00,
            'max_failed_payments' => 1,
            'auto_bill_out_amt' => 0,
            'nsf_fee' => 0.00,
            'ship_to_name' => $this->faker->name,
            'ship_to_street' => $this->faker->streetAddress,
            'ship_to_city' => $this->faker->city,
            'ship_to_state' => $this->faker->state,
            'ship_to_zip' => $this->faker->postcode,
            'ship_to_country' => $this->faker->country,
            'ship_to_phone_num' => $this->faker->streetAddress,
            'transaction_type' => 'Donation',
            'total_billing_cycles' => 0,
            'amt' => random_int(50, 100),
            'currency_code' => 'USD',
            'shipping_amt' => 0.00,
            'tax_amt' => 0.00,
            'init_amt' => 0.00,
            'num_cycles_completed' => 0,
            'outstanding_balance' => 0.00,
            'failed_payment_count' => 0,
            'payment_mutex' => 0,
        ];
    }
}
