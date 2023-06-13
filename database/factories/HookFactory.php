<?php

namespace Database\Factories;

use Ds\Models\Hook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hook::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'active' => true,
            'content_type' => Arr::random(Hook::CONTENT_TYPES),
            'insecure_ssl' => false,
            'payload_url' => $this->faker->url(),
            'secret' => Str::random(32),
        ];
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }

    public function insecure(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'insecure_ssl' => true,
            ];
        });
    }
}
