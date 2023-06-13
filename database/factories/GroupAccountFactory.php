<?php

namespace Database\Factories;

use Ds\Models\GroupAccount;
use Ds\Models\Member as Account;
use Ds\Models\Membership as Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GroupAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_id' => Group::factory(),
            'account_id' => Account::factory(),
            'start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'end_date' => $this->faker->dateTimeBetween('1 day', '5 years'),
            'source' => $this->faker->words(mt_rand(1, 5), true),
        ];
    }
}
