<?php

namespace Database\Factories\Domain\FeaturePreviews\Models;

use Ds\Domain\FeaturePreviews\Models\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserStateFactory extends Factory
{
    protected $model = UserState::class;

    public function definition()
    {
        return [
            'feature' => Str::snake('feature_' . $this->faker->word),
            'user_id' => user('id'),
        ];
    }
}
