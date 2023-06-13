<?php

namespace Database\Factories;

use Ds\Models\Asset;
use Ds\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'theme_id' => Theme::factory(),
            'key' => 'templates/' . $this->faker->slug(3) . '.liquid',
            'content_type' => 'text/x-liquid',
            'size' => function ($attributes) {
                return mb_strlen($attributes['value'] ?? '');
            },
        ];
    }

    public function style(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'styles/' . $this->faker->slug(3) . '.scss',
                'content_type' => 'text/scss',
            ];
        });
    }
}
