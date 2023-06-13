<?php

namespace Database\Factories;

use Ds\Models\Membership;
use Ds\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Variant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'variantname' => ucwords($this->faker->words(3, true)),
            'sequence' => $this->faker->randomDigitNot(0),
            'isdefault' => false,
            'is_donation' => false,
            'price' => $this->faker->numberBetween(1000, 10000) / 100,
            'isshippable' => true,
            'is_shipping_free' => false,
            'weight' => 1,
            'quantity' => 1,
        ];
    }

    public function sku(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'sku' => strtoupper(substr($this->faker->sha1, 0, 8)),
            ];
        });
    }

    public function donation(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_donation' => true,
                'price_presets' => '10,25,50,100,250,other',
                'price' => null,
                'isshippable' => false,
                'is_shipping_free' => false,
                'weight' => null,
            ];
        });
    }

    public function once(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'billing_period' => 'onetime',
            ];
        });
    }

    public function recurring(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'billing_period' => 'monthly',
            ];
        });
    }

    public function dpMembership(?int $dpId = null): self
    {
        return $this->state(function (array $attributes) use ($dpId) {
            return [
                'membership_id' => Membership::factory()->dpMembership($dpId),
            ];
        });
    }

    public function freeShipping(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'isshippable' => true,
                'is_shipping_free' => true,
            ];
        });
    }
}
