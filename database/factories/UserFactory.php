<?php

namespace Database\Factories;

use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Fortify\RecoveryCode;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'hashed_password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'primaryphonenumber' => $this->faker->phoneNumber,
            'alternatephonenumber' => $this->faker->phoneNumber,
            'is_account_admin' => false,
        ];
    }

    /**
     * Indicate that the user is an admin.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_account_admin' => true,
            ];
        });
    }

    /**
     * Indicate that the user has an api token.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function api()
    {
        return $this->state(function (array $attributes) {
            return [
                'api_token' => Str::random(60),
            ];
        });
    }

    /**
     * Include 2FA date for user.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function twoFactorAuthentication()
    {
        return $this->state(function (array $attributes) {
            return [
                'two_factor_secret' => encrypt(Str::random(32)),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ];
        });
    }
}
