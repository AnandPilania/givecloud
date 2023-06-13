<?php

namespace Database\Factories\Domain\Commerce;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'enabled' => true,
            'provider' => 'givecloudtest',
            'provider_type' => 'credit',
            'display_name' => 'Givecloud Test Gateway',
            'test_mode' => true,
        ];
    }

    public function braintree(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'braintree',
                'display_name' => 'Braintree',
                'config' => [
                    'merchant_id' => 'some_id',
                    'api_public_key' => 'some_key',
                    'api_private_key' => 'some_private_key',
                    'merchant_account_id' => [
                        'CAD' => 'gc_test_merchant',
                    ],
                ],
            ];
        });
    }

    public function nmi(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'nmi',
                'display_name' => 'NMI',
            ];
        });
    }

    public function paypalExpress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'paypalexpress',
                'provider_type' => 'paypal',
                'display_name' => 'PayPal Express Checkout',
            ];
        });
    }

    public function safesave(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'safesave',
                'display_name' => 'SafeSave',
            ];
        });
    }

    public function stripe(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'stripe',
                'display_name' => 'Stripe',
                'credential2' => 'sk_test_YMgMzAgMTk6NTE6NDggRUV',
            ];
        });
    }

    public function vanco(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'vanco',
                'display_name' => 'Vanco Payment Solutions',
                'config' => ['encryption_key' => 'qcbXYcp3R5GyD6ogKHAdZHNAYbd75cDv'],
            ];
        });
    }
}
