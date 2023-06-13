<?php

namespace Database\Factories;

use Ds\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromoCode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->regexify('[a-zA-Z0-9\-_]{3,15}'),
            'description' => $this->faker->words(mt_rand(3, 20), true),
            'discount_type' => $discountType = $this->faker->randomElement(['dollar', 'percent']),
            'discount' => isset($discountType) && $discountType === 'percent' ? mt_rand(1, 10000) / 100 : mt_rand(1, 50),
        ];
    }

    public function isFreeShipping(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_free_shipping' => 1,
            ];
        });
    }
}
