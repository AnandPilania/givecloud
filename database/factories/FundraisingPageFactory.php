<?php

namespace Database\Factories;

use Ds\Domain\Commerce\Currency;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundraisingPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FundraisingPage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'title' => $this->faker->words(mt_rand(4, 8), true),
            'category' => $this->faker->words(mt_rand(1, 5), true),
            'currency_code' => array_rand(Currency::getLocalCurrencies()),
            'goal_deadline' => $this->faker->dateTimeBetween('now', '+1 year'),
            'goal_amount' => mt_rand(0, 10000000) / 100,
            'is_team' => false,
            'video_url' => $this->faker->url(),
            'member_organizer_id' => Member::factory(),
        ];
    }

    public function team(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_team' => true,
                'team_name' => $this->faker->words(mt_rand(1, 4), true),
            ];
        });
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'goal_deadline' => $this->faker->dateTimeBetween('+1 day', '+1 year'),
            ];
        });
    }

    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'closed',
            ];
        });
    }

    public function draft(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }

    public function deadlined(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'goal_deadline' => $this->faker->dateTimeBetween(),
            ];
        });
    }

    public function reported(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'report_count' => $this->faker->numberBetween(1),
            ];
        });
    }

    public function suspended(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
            ];
        });
    }
}
