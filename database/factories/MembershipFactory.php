<?php

namespace Database\Factories;

use Ds\Models\Membership;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipFactory extends Factory
{
    use UserstampsTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Membership::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'days_to_expire' => mt_rand(10, 360),
            'description' => $this->faker->paragraph(1),
            'double_optin_required' => $this->faker->boolean(),
            'default_url' => $this->faker->url(),
            'members_can_manage_optin' => $this->faker->boolean(),
            'members_can_manage_optout' => $this->faker->boolean(),
            'members_can_view_directory' => $this->faker->boolean(),
            'name' => $this->faker->words(mt_rand(1, 4), true),
            'public_description' => $this->faker->paragraph(),
            'public_name' => $this->faker->words(mt_rand(1, 4), true),
            'should_display_badge' => $this->faker->boolean(),
            'show_in_profile' => false,
        ];
    }

    public function deleted(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'deleted_by' => $user->getKey(),
                'deleted_at' => $this->faker->dateTime('-1day'),
            ];
        });
    }

    public function dpMembership(?int $dpId = null): self
    {
        return $this->state(function (array $attributes) use ($dpId) {
            return [
                'dp_id' => $dpId ?? $this->faker->randomNumber(4),
            ];
        });
    }

    public function started(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'starts_at' => $this->faker->dateTimeBetween('-1year', '-1day'),
            ];
        });
    }
}
