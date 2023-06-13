<?php

namespace Database\Factories;

use Ds\Enums\RecurringFrequency;
use Ds\Models\OrderItem;
use Ds\Models\Tax;
use Ds\Models\TributeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'price' => mt_rand(0, 10),
            'qty' => mt_rand(1, 5),
            'recurring_frequency' => null,
            'recurring_day' => null,
            'recurring_day_of_week' => null,
            'recurring_with_initial_charge' => $this->faker->boolean(),
            'recurring_with_dpo' => $this->faker->boolean(),
            'is_tribute' => false,
            'tribute_notify_name' => null,
            'tribute_notify_at' => null,
            'tribute_notify_email' => null,
            'tribute_notify_address' => null,
            'tribute_notify_city' => null,
            'tribute_notify_state' => null,
            'tribute_notify_zip' => null,
            'tribute_notify_country' => null,
            'public_message' => null,
            'fundraising_page_id' => null,
            'fundraising_member_id' => null,
            'gift_aid' => $this->faker->boolean(),
            'metadata' => [],
        ];
    }

    public function recurring(string $frequency): self
    {
        if (! in_array($frequency, RecurringFrequency::all(), true)) {
            throw new InvalidArgumentException("[$frequency] is not a valid frequency option for the recurring state.");
        }

        return $this->state(function (array $attributes) use ($frequency) {
            return [
                'price' => $price = mt_rand(1, 10),
                'recurring_amount' => $price,
                'recurring_frequency' => $frequency,
                'recurring_day' => mt_rand(1, 31),
                'recurring_day_of_week' => mt_rand(1, 7),
            ];
        });
    }

    public function dcc(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'dcc_amount' => $this->faker->randomFloat(2, 0.01),
            ];
        });
    }

    public function recurring_dcc(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'dcc_amount' => $this->faker->randomFloat(2, 0.01),
                'dcc_recurring_amount' => $this->faker->randomFloat(2, 0.01),
            ];
        });
    }

    public function taxable(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'taxes' => Tax::factory(),
            ];
        });
    }

    public function tribute(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_tribute' => true,
                'dpo_tribute_id' => null,
                'tribute_type_id' => TributeType::factory(),
                'tribute_name' => $this->faker->words(mt_rand(1, 4), true),
                'tribute_message' => $this->faker->words(mt_rand(0, 15), true),
            ];
        });
    }

    public function tributeEmail(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_tribute' => true,
                'dpo_tribute_id' => null,
                'tribute_type_id' => TributeType::factory(),
                'tribute_name' => $this->faker->words(mt_rand(1, 4), true),
                'tribute_message' => $this->faker->words(mt_rand(0, 15), true),
                'tribute_notify' => 'email',
                'tribute_notify_name' => $this->faker->words(mt_rand(1, 4), true),
                'tribute_notify_at' => $this->faker->dateTimeInInterval('now', '+1 year'),
                'tribute_notify_email' => $this->faker->email(),
            ];
        });
    }

    public function tributeLetter(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_tribute' => true,
                'dpo_tribute_id' => null,
                'tribute_type_id' => TributeType::factory(),
                'tribute_name' => $this->faker->words(mt_rand(1, 4), true),
                'tribute_message' => $this->faker->words(mt_rand(0, 15), true),
                'tribute_notify' => 'letter',
                'tribute_notify_name' => $this->faker->words(mt_rand(1, 4), true),
                'tribute_notify_at' => $this->faker->dateTimeInInterval('now', '+1 year'),
                'tribute_notify_address' => $this->faker->streetAddress(),
                'tribute_notify_city' => $this->faker->city(),
                'tribute_notify_state' => $this->faker->state(),
                'tribute_notify_zip' => $this->faker->postcode(),
                'tribute_notify_country' => $this->faker->countryCode(),
            ];
        });
    }
}
