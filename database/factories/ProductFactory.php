<?php

namespace Database\Factories;

use Ds\Enums\DesignationOptionsType;
use Ds\Enums\ProductType;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Xmeltrut\Autop\Autop;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => strtoupper(substr($this->faker->sha1, 0, 8)),
            'name' => $this->faker->colorName . ' ' . $this->faker->productName,
            'summary' => $this->faker->sentence,
            'description' => Autop::format($this->faker->paragraphs(mt_rand(1, 10), true)),
            'base_currency' => sys_get('dpo_currency'),
            'isenabled' => true,
            'show_in_pos' => true,
            'hide_price' => false,
            'hide_qty' => false,
            'is_tax_receiptable' => false,
            'outofstock_allow' => false,
        ];
    }

    public function donation(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => ucwords($this->faker->word) . ' Fund',
                'hide_price' => true,
                'hide_qty' => true,
                'is_tax_receiptable' => true,
            ];
        });
    }

    public function allowOutOfStock(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'outofstock_allow' => true,
            ];
        });
    }

    public function designationOptionsForSingleAccount(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'designation_options' => [
                    'type' => DesignationOptionsType::SINGLE_ACCOUNT,
                    'default_account' => 'GENERAL_FUND',
                    'designations' => [
                        ['label' => '', 'account' => 'GENERAL_FUND', 'is_default' => true],
                    ],
                ],
            ];
        });
    }

    public function designationOptionsForSupportersChoice(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'designation_options' => [
                    'type' => DesignationOptionsType::SUPPORTERS_CHOICE,
                    'default_account' => 'GENERAL_FUND',
                    'designations' => [
                        ['label' => 'Benevolent Fund', 'account' => 'BENEVOLENT_FUND', 'is_default' => false],
                        ['label' => 'Building Fund', 'account' => 'BUILDING_FUND', 'is_default' => false],
                        ['label' => 'General Fund', 'account' => 'GENERAL_FUND', 'is_default' => true],
                    ],
                ],
            ];
        });
    }

    public function donationForm(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ProductType::DONATION_FORM,
            ];
        });
    }

    public function permalink(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'permalink' => $this->faker->slug,
            ];
        });
    }

    public function receiptable(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_tax_receiptable' => true,
            ];
        });
    }
}
