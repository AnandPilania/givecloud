<?php

namespace Database\Factories;

use Ds\Models\TransientLog;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransientLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransientLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'origin' => 'web',
            'level' => 'info',
            'request_id' => (string) Str::orderedUuid(),
            'user_id' => User::factory(),
            'source' => $this->faker->randomElement(['donorperfect', 'taxcloud']),
            'message' => $this->faker->sentence,
            'context' => null,
            'ip_address' => $this->faker->ipv4,
        ];
    }
}
