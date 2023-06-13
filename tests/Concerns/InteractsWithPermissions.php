<?php

namespace Tests\Concerns;

use DomainException;
use Ds\Models\User;
use Illuminate\Support\Arr;

trait InteractsWithPermissions
{
    /**
     * @param array|string $permissions
     */
    protected function withUserPermissions($permissions = [], string $guard = 'web'): self
    {
        $user = $this->app['auth']->guard($guard)->user();

        if (empty($user)) {
            throw new DomainException("Authenticate user with [$guard] guard prior to using withPermissions");
        }

        $user->permissions_json = Arr::wrap($permissions);
        $user->save();

        return $this;
    }

    /**
     * @param array|string $permissions
     */
    protected function withPassportUserPermissions($permissions = []): self
    {
        return $this->withUserPermissions($permissions, 'passport');
    }

    /**
     * @param array|string $permissions
     */
    protected function createUserWithPermissions($permissions = [], array $attributes = []): User
    {
        return User::factory()->create(array_merge(
            ['permissions_json' => is_array($permissions) ? $permissions : [$permissions]],
            $attributes
        ));
    }
}
