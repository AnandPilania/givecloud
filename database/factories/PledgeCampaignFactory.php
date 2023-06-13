<?php

namespace Database\Factories;

use Carbon\Carbon;
use Ds\Models\PledgeCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class PledgeCampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PledgeCampaign::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $startDate = Carbon::now()->startOfDay()->addMonths(mt_rand(0, 12));
        $endDate = $startDate->copy()->addMonths(mt_rand(1, 12));

        return [
            'name' => $this->faker->sentence(3),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
