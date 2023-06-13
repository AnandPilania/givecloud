<?php

namespace Database\Factories;

use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_pos' => false,
            'is_test' => false,
            'client_uuid' => strtoupper(uuid()),
            'started_at' => now(),
            'totalamount' => $this->faker->randomFloat(2, 0.01),
            'currency_code' => sys_get('dpo_currency'),
            'functional_currency_code' => sys_get('dpo_currency'),
            'functional_total' => fn ($attributes) => $attributes['totalamount'],
            'ship_to_billing' => true,
            'billingcountry' => sys_get('default_country'),
            'shipcountry' => sys_get('default_country'),
            'tax_receipt_type' => sys_get('tax_receipt_type'),
            'dp_sync_order' => sys_get('bool:dp_auto_sync_orders'),
            'source' => 'Web',
            'member_id' => Member::factory(),
            'dcc_total_amount' => $this->faker->randomFloat(2),
            'shipping_amount' => $this->faker->randomFloat(2),
            'taxtotal' => $this->faker->randomFloat(2),
            'timezone' => $this->faker->timezone,
            'language' => 'en_CA',
        ];
    }

    public function anonymous(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_anonymous' => true,
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'iscomplete' => true,
            ];
        });
    }

    public function conversation(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'source' => 'Messenger',
            ];
        });
    }

    public function courier(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'courier_method' => $this->faker->words(mt_rand(1, 3), true) . ': ' . $this->faker->words(mt_rand(1, 3), true),
            ];
        });
    }

    public function dcc(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'dcc_total_amount' => $this->faker->randomFloat(2, 0.01),
            ];
        });
    }

    public function freeShipping(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_free_shipping' => true,
            ];
        });
    }

    public function paid(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_processed' => true,
                'invoicenumber' => $attributes['client_uuid'],
                'confirmationdatetime' => now(),
                'ordered_at' => now(),
                'totalamount' => $this->faker->randomFloat(2),
            ];
        });
    }

    public function pointOfSale(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_pos' => true,
            ];
        });
    }

    public function public(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_anonymous' => false,
            ];
        });
    }

    public function taxed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'taxtotal' => $this->faker->randomFloat(2, 0.01),
            ];
        });
    }

    public function shipped(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'shipping_amount' => $this->faker->randomFloat(2, 0.01),
                'shipping_method_id' => ShippingMethod::factory(),
            ];
        });
    }
}
