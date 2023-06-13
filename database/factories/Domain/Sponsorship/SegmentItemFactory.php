<?php

namespace Database\Factories\Domain\Sponsorship;

use Ds\Domain\Sponsorship\Models\SegmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SegmentItemFactory extends Factory
{
    protected $model = SegmentItem::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'summary' => $this->faker->words(3, true),
        ];
    }
}
