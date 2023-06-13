<?php

namespace Database\Factories;

use Ds\Models\Media;
use Ds\Models\PostType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $name = $this->faker->unique()->word(),
            'rss_copyright' => $this->faker->words(mt_rand(1, 5), true),
            'rss_description' => $this->faker->paragraph(1),
            'sysname' => 'blog',
            'url_slug' => Str::slug($name),
        ];
    }

    public function snippet(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'sysname' => 'snippet',
            ];
        });
    }

    public function withPhoto(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'media_id' => Media::factory(),
            ];
        });
    }
}
