<?php

namespace Database\Factories;

use Ds\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Theme::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'handle' => $this->faker->slug(1),
            'title' => $this->faker->words(2, true),
            'description' => $this->faker->words(8, true),
            'source' => 'repo',
            'locked' => true,
        ];
    }

    public function unlocked(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'locked' => false,
            ];
        });
    }
}
