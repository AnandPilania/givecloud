<?php

namespace Database\Factories\Domain\Sponsorship;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Models\Member;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sponsor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sponsorship_id' => Sponsorship::factory(),
            'member_id' => Member::factory(),
            'started_at' => now(),
            'started_by' => function (array $attributes) {
                return $attributes['member_id'];
            },
        ];
    }

    public function ended(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'ended_at' => now()->subMonths(3),
                'ended_by' => User::factory(),
            ];
        });
    }
}
