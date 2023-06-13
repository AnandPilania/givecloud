<?php

namespace Database\Factories;

use Ds\Models\Hook;
use Ds\Models\HookDelivery;
use Ds\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HookDeliveryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HookDelivery::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'hook_id' => Hook::factory(),
            'event' => function ($attributes) {
                $hook = Hook::find($attributes['hook_id']);

                return Arr::random(Arr::wrap($hook->events->map->name ?? $this->faker->regexify('[a-z]+\.[a-z]+')));
            },
            'guid' => (string) Str::uuid(),
            'req_body' => function ($attributes) {
                return Member::factory()->create()->toJson();
            },
            'delivered_at' => now(),
            'completed_in' => 0,
        ];
    }
}
