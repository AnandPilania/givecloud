<?php

namespace Database\Factories;

use Ds\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    public function jpeg(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'collection_name' => 'files',
                'name' => $this->faker->slug(3),
                'filename' => function ($attributes) {
                    return $attributes['name'] . '.jpg';
                },
                'content_type' => 'image/jpeg',
                'size' => $this->faker->randomNumber(5),
            ];
        });
    }

    public function pdf(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'collection_name' => 'files',
                'name' => $this->faker->slug(3),
                'filename' => function ($attributes) {
                    return $attributes['name'] . '.pdf';
                },
                'content_type' => 'application/pdf',
                'size' => $this->faker->randomNumber(5),
            ];
        });
    }
}
