<?php

namespace Database\Factories\Domain\Sponsorship;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorshipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sponsorship::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_enabled' => true,
            'reference_number' => $this->faker->ein,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'biography' => $this->faker->paragraphs(3, true),
            'street_number' => $this->faker->buildingNumber,
            'street_name' => $this->faker->streetName,
            'country' => $this->faker->countryCode,
            'gender' => $this->faker->randomElement(['M', 'F']),
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
