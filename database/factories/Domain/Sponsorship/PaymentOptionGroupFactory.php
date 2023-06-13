<?php

namespace Database\Factories\Domain\Sponsorship;

use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentOptionGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentOptionGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => ucwords($this->faker->words(3, true)),
        ];
    }
}
