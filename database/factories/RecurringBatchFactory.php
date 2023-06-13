<?php

namespace Database\Factories;

use Ds\Models\RecurringBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringBatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecurringBatch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'batched_on' => now(),
            'started_at' => now(),
            'max_simultaneous' => 1,
            'accounts_count' => 0,
        ];
    }

    public function finished(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'started_at' => now()->subMinutes(10),
                'finished_at' => now(),
                'elapsed_time' => 600,
            ];
        });
    }
}
