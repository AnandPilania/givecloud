<?php

namespace Database\Factories;

use Ds\Models\SocialIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialIdentityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SocialIdentity::class;

    public function definition(): array
    {
        return [
            'provider_name' => 'google',
            'provider_id' => $this->faker->uuid,
        ];
    }

    public function confirmed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_confirmed' => true,
            ];
        });
    }

    public function unconfirmed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_confirmed' => false,
            ];
        });
    }
}
