<?php

namespace Database\Factories;

use Ds\Models\ProductCustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCustomFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductCustomField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    public function jsonOptions(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'format' => 'advanced',
                'options' => collect(range(0, mt_rand(2, 10)))
                    ->map(function () {
                        return [
                            'label' => $this->faker->words(mt_rand(1, 5), true),
                            'value' => $this->faker->words(mt_rand(1, 3), true),
                        ];
                    })->toJson(),
            ];
        });
    }

    public function simpleOptions(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'format' => '', // empty is "simple" options string
                'options' => collect(range(0, mt_rand(2, 10)))
                    ->map(function () {
                        return $this->faker->words(mt_rand(1, 5), true);
                    })->implode("\r\n"),
            ];
        });
    }
}
