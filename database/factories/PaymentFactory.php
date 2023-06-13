<?php

namespace Database\Factories;

use Ds\Enums\CardBrand;
use Ds\Enums\PaymentType;
use Ds\Models\Member;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'livemode' => false,
            'source_account_id' => Member::factory(),

            'amount' => $amount = mt_rand(0, 1000000) / 100,
            'functional_exchange_rate' => mt_rand(0, 1000000) / 100,
            'functional_total' => mt_rand(0, 1000000) / 100,

            'paid' => $paid = (bool) mt_rand(0, 1),
            'captured' => $captured = ! $paid && (bool) mt_rand(0, 1),
            'captured_at' => $captured ? Carbon::now()->subDays(mt_rand(1, 400))->format('Y-m-d\TH:i:s\Z') : null,
            'refunded' => $refunded = $paid && (bool) mt_rand(0, 1),
            'amount_refunded' => $refunded ? mt_rand(0, $amount * 100) / 100 : 0,
            'type' => Arr::random(PaymentType::all()),
            'source_payment_method_id' => PaymentMethod::factory(),
        ];
    }

    public function by(Member $supporter): Factory
    {
        return $this->state(function (array $attributes) use ($supporter) {
            return [
                'source_account_id' => $supporter,
            ];
        });
    }

    public function paid(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'paid' => true,
                'refunded' => false,
            ];
        });
    }

    public function refunded(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'refunded' => true,
            ];
        });
    }

    public function card(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'card',
                'card_brand' => Arr::random(CardBrand::all()),
                'card_exp_month' => mt_rand(1, 12),
                'card_exp_year' => date('Y') + 3,
            ];
        });
    }
}
