<?php

namespace Database\Factories;

use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Models\AccountType;
use Ds\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $gender = array_rand(['male', 'female']);
        $title = $this->faker->title($gender);
        $firstName = $this->faker->firstName($gender);
        $lastName = $this->faker->lastName;
        $email = strtolower("$firstName.$lastName@example.com");
        $company = array_rand([null, $this->faker->company]);
        $phone = $this->faker->phoneNumber;

        return array_merge([
            'title' => $title,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => bcrypt('abc123'),
            'is_active' => true,
            'bill_title' => $title,
            'bill_first_name' => $firstName,
            'bill_last_name' => $lastName,
            'bill_organization_name' => $company,
            'bill_phone' => $phone,
            'bill_email' => $email,
            'ship_title' => $title,
            'ship_first_name' => $firstName,
            'ship_last_name' => $lastName,
            'ship_organization_name' => $company,
            'ship_phone' => $phone,
            'ship_email' => $email,
        ], $this->formatAddress($this->faker->americanAddress));
    }

    public function guest(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => null,
                'password' => null,
            ];
        });
    }

    public function australian(): self
    {
        return $this->state(function (array $attributes) {
            return $this->formatAddress($this->faker->australianAddress);
        });
    }

    public function canadian(): self
    {
        return $this->state(function (array $attributes) {
            return $this->formatAddress($this->faker->canadianAddress) + [
                'bill_phone' => $canadianNumber = $this->faker->canadianPhoneNumber,
                'ship_phone' => $canadianNumber,
            ];
        });
    }

    public function british(): self
    {
        return $this->state(function (array $attributes) {
            return $this->formatAddress($this->faker->britishAddress);
        });
    }

    public function american(): self
    {
        return $this->state(function (array $attributes) {
            return $this->formatAddress($this->faker->americanAddress);
        });
    }

    public function nps(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'nps' => mt_rand(1, 10),
            ];
        });
    }

    public function individual(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'account_type_id' => AccountType::firstOrCreate(
                    ['is_organization' => false],
                    ['name' => 'Individual'],
                ),
                'bill_organization_name' => null,
                'ship_organization_name' => null,
            ];
        });
    }

    public function organization(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'account_type_id' => AccountType::firstOrCreate(
                    ['is_organization' => true],
                    ['name' => 'Organization'],
                ),
                'bill_organization_name' => $companyName = $this->faker->company,
                'ship_organization_name' => $companyName,
            ];
        });
    }

    private function formatAddress(array $address): array
    {
        return [
            'bill_address_01' => $address['address_01'],
            'bill_address_02' => $address['address_02'],
            'bill_city' => $address['city'],
            'bill_state' => $address['state'],
            'bill_zip' => $address['zip'],
            'bill_country' => $address['country'],
            'ship_address_01' => $address['address_01'],
            'ship_address_02' => $address['address_02'],
            'ship_city' => $address['city'],
            'ship_state' => $address['state'],
            'ship_zip' => $address['zip'],
            'ship_country' => $address['country'],
        ];
    }

    public function archived(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function denied(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_status' => SupporterVerifiedStatus::DENIED,
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_status' => SupporterVerifiedStatus::PENDING,
            ];
        });
    }

    public function unverified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_status' => null,
            ];
        });
    }

    public function verified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'verified_status' => SupporterVerifiedStatus::VERIFIED,
            ];
        });
    }

    public function withLifetimeAggregatedValues(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'lifetime_donation_amount' => $this->faker->randomFloat(2),
                'lifetime_fundraising_amount' => $this->faker->randomFloat(2),
                'lifetime_purchase_amount' => $this->faker->randomFloat(2),
                'lifetime_donation_count' => $this->faker->numberBetween(0, 200),
                'lifetime_fundraising_count' => $this->faker->numberBetween(0, 200),
                'lifetime_purchase_count' => $this->faker->numberBetween(0, 200),
            ];
        });
    }
}
