<?php

namespace Database\Factories;

use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\ExternalReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalReferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExternalReference::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'reference' => $this->faker->uuid,
        ];
    }

    public function salesforce(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'service' => ExternalReferenceService::SALESFORCE,
            ];
        });
    }

    public function supporter(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ExternalReferenceType::SUPPORTER,
            ];
        });
    }
}
