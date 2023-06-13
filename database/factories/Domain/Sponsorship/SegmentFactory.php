<?php

namespace Database\Factories\Domain\Sponsorship;

use Database\Factories\UserstampsTrait;
use Ds\Domain\Sponsorship\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SegmentFactory extends Factory
{
    use UserstampsTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Segment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'type' => 'text',
        ];
    }

    public function multiSelect(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'mutli-select',
            ];
        });
    }
}
