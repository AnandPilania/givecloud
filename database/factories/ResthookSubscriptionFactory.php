<?php

namespace Database\Factories;

use Ds\Models\ResthookSubscription;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResthookSubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ResthookSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event' => implode('.', $this->faker->words(2)),
            'target_url' => $this->faker->url(),
            'user_id' => User::factory()->create()->getKey(),
        ];
    }
}
