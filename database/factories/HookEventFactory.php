<?php

namespace Database\Factories;

use Ds\Models\HookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class HookEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HookEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => HookEvent::getEnabledEvents()->random(),
        ];
    }
}
