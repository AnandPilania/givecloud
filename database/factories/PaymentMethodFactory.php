<?php

namespace Database\Factories;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Member;
use Ds\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentMethodFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => 'ACTIVE',
            'display_name' => 'My payment method',
            'token' => Str::random(12),
            'account_last_four' => $this->randomAccountLastFour(),
            'member_id' => Member::factory(),
            'payment_provider_id' => function ($attributes) {
                return PaymentProvider::provider('givecloudtest')->first();
            },
        ];
    }

    private function randomAccountLastFour(): string
    {
        // cards with these last4s trigger special behaviours in the test gateway
        // ex failed AVS/CVC, etc. we should avoid randomly assigning them.
        $specialLastFours = ['0036', '0127', '9995', '9987'];

        do {
            $last4 = str_pad($this->faker->randomNumber(4), 4, '0', STR_PAD_LEFT);
        } while (in_array($last4, $specialLastFours, true));

        return $last4;
    }

    public function bankAccount(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'display_name' => 'My checking account',
                'account_type' => 'Personal Checking',
                'ach_bank_name' => 'FEDERAL RESERVE BANK',
                'ach_routing' => '011000015',
                'ach_account_type' => 'checking',
                'ach_entity_type' => 'personal',
            ];
        });
    }

    public function creditCard(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'display_name' => function ($attributes) {
                    return $attributes['account_type'];
                },
                'account_type' => $this->faker->creditCardType,
                'cc_expiry' => $this->faker->dateTimeBetween('+1 year', '+3 years')->format('Y-m-d'),
            ];
        });
    }

    public function insufficientFunds(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'account_last_four' => '9995',
            ];
        });
    }

    public function expiringByEndOfNextMonth(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'display_name' => 'Expiring by end of month Payment',
                'cc_expiry' => toUtc('today')->addMonthWithoutOverflow()->endOfMonth(),
            ];
        });
    }

    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'display_name' => 'Expired Payment',
                'cc_expiry' => toUtc('today')->subMonthWithoutOverflow()->endOfMonth(),
            ];
        });
    }

    public function tokenizing(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PENDING',
                'display_name' => '',
                'token' => null,
                'account_last_four' => null,
            ];
        });
    }
}
