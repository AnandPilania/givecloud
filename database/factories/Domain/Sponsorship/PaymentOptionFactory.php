<?php

namespace Database\Factories\Domain\Sponsorship;

use Ds\Domain\Sponsorship\Models\PaymentOption;
use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Enums\RecurringFrequency;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

class PaymentOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentOption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_id' => PaymentOptionGroup::factory(),
            'sequence' => $this->faker->randomDigitNot(0),
            'amount' => $this->faker->numberBetween(1000, 10000) / 100,
        ];
    }

    public function recurring(string $frequency): self
    {
        if (! in_array($frequency, RecurringFrequency::all(), true)) {
            throw new InvalidArgumentException("[$frequency] is not a valid frequency option for the recurring state.");
        }

        return $this->state(function (array $attributes) use ($frequency) {
            return [
                'is_recurring' => true,
                'recurring_frequency' => $frequency,
                'recurring_day' => mt_rand(1, 31),
                'recurring_day_of_week' => mt_rand(1, 7),
            ];
        });
    }
}
