<?php

namespace Database\Factories;

use Ds\Models\MemberLogin;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberLoginFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MemberLogin::class;

    public function definition(): array
    {
        return [
            'user_agent' => $this->faker->userAgent,
            'ip' => $this->faker->ipv4,
        ];
    }

    public function impersonated(?User $user = null): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'impersonated_by' => $user ?? User::factory(),
            ];
        });
    }
}
