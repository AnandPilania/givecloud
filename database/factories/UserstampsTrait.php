<?php

namespace Database\Factories;

use Ds\Models\User;

trait UserstampsTrait
{
    public function by(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'created_by' => $user->getKey(),
                'updated_by' => $user->getKey(),
            ];
        });
    }
}
