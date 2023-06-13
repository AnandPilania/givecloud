<?php

namespace Database\Factories;

use Ds\Models\UserDefinedField;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDefinedFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserDefinedField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $fieldType = $this->faker->randomElement(UserDefinedField::FIELD_TYPES);

        return [
            'entity' => $this->faker->randomElement(UserDefinedField::ENTITIES),
            'field_attributes' => $this->faker->randomElement($this->fieldAttributesValues()[$fieldType]),
            'field_type' => $fieldType,
            'name' => $this->faker->words(mt_rand(1, 5), true),
        ];
    }

    public function choice(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'field_type' => 'choice',
                'field_attributes' => $this->faker->randomElement($this->fieldAttributesChoice()),
            ];
        });
    }

    public function multipleChoice(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'field_type' => 'multiple_choice',
                'field_attributes' => $this->faker->randomElement($this->fieldAttributesMultipleChoice()),
            ];
        });
    }

    public function radio(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'field_type' => 'radio',
                'field_attributes' => $this->faker->randomElement($this->fieldAttributesRadio()),
            ];
        });
    }

    public function shortText(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'field_type' => 'short_text',
                'field_attributes' => $this->faker->randomElement($this->fieldAttributesShortText()),
            ];
        });
    }

    protected function fieldAttributesValues(): array
    {
        return ['short_text' => $this->fieldAttributesShortText()]
            + ['choice' => $this->fieldAttributesChoice()]
            + ['multiple_choice' => $this->fieldAttributesMultipleChoice()]
            + ['radio' => $this->fieldAttributesRadio()];
    }

    protected function fieldAttributesChoice(): array
    {
        return [
            ['options' => range(1, 5)],
            ['options' => ['Google', 'Facebook', 'Friend']],
            ['options' => ['Developer', 'Customer Success', 'CEO', 'Salesman']],
        ];
    }

    protected function fieldAttributesMultipleChoice(): array
    {
        return [
            ['options' => ['Blue', 'Orange', 'Red']],
        ];
    }

    protected function fieldAttributesRadio(): array
    {
        return [
            ['options' => range(1, 5)],
            ['options' => ['Blue', 'Orange', 'Red', 'Green']],
            ['options' => ['Canada', 'United States', 'United Kingdom', 'Mexico', 'France', 'Spain']],
        ];
    }

    protected function fieldAttributesShortText(): array
    {
        return [
            ['required' => true],
            ['type' => 'number'],
            ['required' => true, 'type' => 'number'],
        ];
    }
}
