<?php

namespace Database\Factories;

use Ds\Models\MonitoringIncident;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonitoringIncidentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitoringIncident::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'incident_type' => 'arm_below_threshold',
            'triggered_at' => now(),
            'action_taken' => 'none',
        ];
    }

    public function alwaysRequireCaptcha(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'action_taken' => 'always_require_captcha',
            ];
        });
    }

    public function stopAcceptingPayments(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'action_taken' => 'stop_accepting_payments',
            ];
        });
    }
}
