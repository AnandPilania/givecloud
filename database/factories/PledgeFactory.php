<?php

namespace Database\Factories;

use Ds\Models\Member;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class PledgeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pledge::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_id' => Member::factory(),
            'pledge_campaign_id' => PledgeCampaign::factory(),
        ];
    }
}
