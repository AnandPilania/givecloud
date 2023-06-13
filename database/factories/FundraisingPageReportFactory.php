<?php

namespace Database\Factories;

use Ds\Models\FundraisingPageReport;
use Ds\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundraisingPageReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FundraisingPageReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'reason' => $this->faker->randomElement([
                'Commercial or self promotion',
                'Contains illegal content or activity',
                'Discrimination',
                'Goes against my beliefs, values or politics',
                'Public shaming',
                'Uncivil, rude or vulgar',
            ]),
            'member_id' => Member::factory(),
            'reported_at' => now(),
        ];
    }
}
