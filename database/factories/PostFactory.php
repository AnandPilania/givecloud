<?php

namespace Database\Factories;

use Ds\Models\Media;
use Ds\Models\Post;
use Ds\Models\PostType;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'author' => $this->faker->name,
            'body' => $this->faker->paragraphs(3, true),
            'description' => $this->faker->paragraph(mt_rand(1, 5)),
            'isenabled' => true,
            'modifiedbyuserid' => User::factory(),
            'name' => $this->faker->unique()->words(mt_rand(1, 5), true),
            'postdatetime' => Carbon::now()->sub('1 hour'),
            'type' => PostType::factory(),
            'url_slug' => $this->faker->slug,
        ];
    }

    public function snippet(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => PostType::factory()->snippet(),
            ];
        });
    }

    public function withImages(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'alt_image_id' => Media::factory()->jpeg(),
                'featured_image_id' => Media::factory()->jpeg(),
            ];
        });
    }

    public function withTags(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'tags' => collect(range(1, mt_rand(1, 4)))->map(function () {
                    return $this->faker->unique()->words(mt_rand(1, 3), true);
                })->implode(','),
            ];
        });
    }
}
