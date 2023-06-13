<?php

namespace Database\Factories;

use Ds\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;
use Xmeltrut\Autop\Autop;

class NodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Node::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parentid' => 0,
            'type' => 'html',
            'isactive' => 1,
            'title' => $this->faker->words(mt_rand(3, 12), true),
            'body' => Autop::format($this->faker->paragraphs(mt_rand(1, 10), true)),
            'url' => $this->faker->slug,
        ];
    }

    public function navNenu(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'parentid' => 0,
                'type' => 'menu',
                'title' => ucwords($this->faker->word) . ' Menu',
                'body' => null,
                'url' => null,
            ];
        });
    }
}
