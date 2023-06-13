<?php

namespace Database\Factories;

use Ds\Models\Comment;
use Ds\Models\Member;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'body' => $this->faker->paragraph(),
            'commentable_id' => Member::factory(),
            'commentable_type' => (new Member)->getMorphClass(),
        ];
    }

    public function by(User $user): self
    {
        return $this->state(function () use ($user) {
            return [
                'created_by' => $user->getKey(),
                'updated_by' => $user->getKey(),
            ];
        });
    }
}
