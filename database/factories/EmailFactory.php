<?php

namespace Database\Factories;

use Carbon\Carbon;
use Ds\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Email::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $nameWords = $this->faker->words(mt_rand(2, 5));
        $name = implode(' ', $nameWords);
        shuffle($nameWords);

        return [
            'body_template' => $this->faker->randomHtml(),
            'name' => $name,
            'subject' => $this->faker->words(mt_rand(2, 8), true),
            'type' => implode('_', $nameWords),
        ];
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'active_start_date' => Carbon::now()->subDay(),
                'is_active' => true,
            ];
        });
    }
}
