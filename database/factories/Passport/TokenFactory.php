<?php

namespace Database\Factories\Passport;

use Carbon\Carbon;
use Ds\Models\Passport\Token as PassportToken;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PassportToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::random(40),
            'expires_at' => Carbon::now()->addDay(),
            'revoked' => false,
            'user_id' => User::factory(),
        ];
    }

    public function revoked(): self
    {
        return $this->state(function (array $attributes) {
            return ['revoked' => true];
        });
    }
}
