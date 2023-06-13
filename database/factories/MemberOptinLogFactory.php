<?php

namespace Database\Factories;

use Ds\Enums\MemberOptinAction;
use Ds\Enums\MemberOptinSource;
use Ds\Models\MemberOptinLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class MemberOptinLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MemberOptinLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'action' => Arr::random(MemberOptinAction::all()),
            'ip' => $this->faker->ipv4,
            'reason' => null,
            'source' => Arr::random(MemberOptinSource::all()),
            'user_agent' => $this->faker->userAgent,
        ];
    }

    public function optin(): self
    {
        return $this->state(function (array $attributes): array {
            return [
                'action' => MemberOptinAction::OPTIN,
            ];
        });
    }

    public function optout(?string $reason = null): self
    {
        return $this->state(function (array $attributes) use ($reason): array {
            return [
                'action' => MemberOptinAction::OPTOUT,
                'reason' => $reason,
            ];
        });
    }
}
