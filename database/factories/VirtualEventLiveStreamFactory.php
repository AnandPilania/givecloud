<?php

namespace Database\Factories;

use Ds\Models\VirtualEvent;
use Ds\Models\VirtualEventLiveStream;
use Illuminate\Database\Eloquent\Factories\Factory;

class VirtualEventLiveStreamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VirtualEventLiveStream::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'virtual_event_id' => VirtualEvent::factory(),
            'stream_id' => $this->faker->slug,
            'stream_key' => $this->faker->slug,
            'status' => 'idle',
        ];
    }
}
