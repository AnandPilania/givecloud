<?php

namespace Database\Factories;

use Carbon\Carbon;
use Ds\Models\PledgeCampaign;
use Ds\Models\Product;
use Ds\Models\VirtualEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class VirtualEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VirtualEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $product = Product::factory()->donation()->create();
        $campaign = PledgeCampaign::factory()->create();
        $campaign->products()->attach($product);

        return [
            'name' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'start_date' => Carbon::now(),
            'campaign_id' => $campaign->id,
            'logo' => 'https://cdn.givecloud.co/static/branding/logo/primary/logo/full_color/digital/givecloud-logo-full-color-rgb.png',
            'background_image' => 'https://images.unsplash.com/photo-1616649003723-30db72c1ed46?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=2850&q=80',
            'video_source' => 'vimeo',
            'video_id' => '481401899',
            'chat_id' => '481401899',
            'tab_one_label' => 'Donate Now',
            'tab_one_product_id' => $product->id,
        ];
    }
}
