<?php

namespace Database\Factories\Domain\Messenger;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationRecipientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ConversationRecipient::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    public function twilio(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'identifier' => $this->faker->e164PhoneNumber(),
                'resource_type' => 'phone_number',
                'twilio_sid' => uniqid('sid_'),
            ];
        });
    }

    public function tollFree(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'identifier' => $this->faker->tollFreePhoneNumber(),
                'resource_type' => 'phone_number',
            ];
        });
    }
}
